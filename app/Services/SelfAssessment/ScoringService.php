<?php

namespace App\Services\SelfAssessment;

use App\Models\SelfAssessment;
use App\Models\User;
use App\Models\Player;
use Illuminate\Support\Carbon;

/**
 * ScoringService — Engine scoring untuk Self-Assessment Telucup.
 *
 * KARAKTERISTIK:
 *   - Deterministik: input yang sama menghasilkan output yang sama.
 *   - Auditable: setiap keputusan dapat ditelusuri (score_breakdown, flags).
 *   - Versioned: versi algoritma di-track agar keputusan lama reproducible.
 *   - Defensible: berbasis instrumen ilmiah yang sudah tervalidasi.
 *
 * ALGORITMA:
 *   1. Validasi & normalisasi jawaban.
 *   2. Hitung skor per pertanyaan (rule-based).
 *   3. Agregasi skor per domain (cardio / msk / acute / psycho).
 *   4. Hitung skor total terbobot.
 *   5. Deteksi red flags & yellow flags.
 *   6. Klasifikasi: Red flag → HIGH. Skor total → mapping LOW/MEDIUM/HIGH.
 *   7. Modifier: faktor kacamata, usia, BMI ekstrem → adjust pemantauan.
 *   8. Generate rekomendasi & ringkasan untuk panitia.
 *
 * MAPPING SKOR (total_score 0-100, semakin tinggi = semakin berisiko):
 *   - 0-25   : LOW
 *   - 26-50  : MEDIUM
 *   - 51-100 : HIGH
 *   - Any red flag → HIGH (override skor)
 */
class ScoringService
{
    /**
     * Versi algoritma. WAJIB di-bump kalau logika scoring diubah.
     */
    public const VERSION = '1.0.0';

    /**
     * Threshold klasifikasi total skor.
     */
    private const THRESHOLD_LOW_MAX    = 25;
    private const THRESHOLD_MEDIUM_MAX = 50;

    /**
     * Jumlah yellow flag yang memicu escalation otomatis ke MEDIUM
     * (meski total skor numerik masih di bawah threshold low).
     * Rasionale: akumulasi banyak sinyal kekhawatiran tetap perlu pemantauan.
     */
    private const YELLOW_FLAG_ESCALATION_THRESHOLD = 3;

    /**
     * Masa berlaku self-assessment (bulan).
     * PAR-Q+ original = 12 bulan, kami pilih 6 bulan untuk konteks kompetisi kampus.
     */
    private const VALIDITY_MONTHS = 6;

    public function __construct(
        private readonly QuestionBankService $questionBank
    ) {}

    /**
     * Entry point: hitung skor & klasifikasi dari jawaban mentah.
     *
     * @param  array  $answers   ['question_code' => answer, ...]
     * @param  User   $user      User peserta (untuk snapshot is_kacamata)
     * @param  Player|null $player  Player (untuk snapshot sport_branch)
     * @return array             Payload siap simpan ke SelfAssessment
     */
    public function score(array $answers, User $user, ?Player $player = null): array
    {
        $questions = $this->questionBank->getAllQuestionsFlat();

        // === Step 1: Snapshot demografi ===
        $demo = $this->extractDemographics($answers);

        // === Step 2: Hitung skor per pertanyaan ===
        $breakdown = [];
        $redFlags = [];
        $yellowFlags = [];
        $domainPoints = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
        $domainMaxPoints = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];

        foreach ($questions as $code => $q) {
            $domain = $q['domain'];

            // Skip section demografi dari scoring
            if ($domain === 'DEMO') {
                continue;
            }

            $answer = $answers[$code] ?? null;
            $result = $this->scoreOneQuestion($q, $answer);

            $breakdown[$code] = $result;

            if ($result['is_red_flag']) {
                $redFlags[] = [
                    'code'   => $code,
                    'text'   => $q['text'],
                    'reason' => $result['reason'],
                ];
            }
            if ($result['is_yellow_flag']) {
                $yellowFlags[] = [
                    'code'   => $code,
                    'text'   => $q['text'],
                    'reason' => $result['reason'],
                ];
            }

            $domainPoints[$domain] += $result['points'];
            $domainMaxPoints[$domain] += $result['max_points'];
        }

        // === Step 3: Normalisasi skor per domain ke 0-100 ===
        $scoreCardio = $this->normalizeScore($domainPoints['A'], $domainMaxPoints['A']);
        $scoreMsk    = $this->normalizeScore($domainPoints['B'], $domainMaxPoints['B']);
        $scoreAcute  = $this->normalizeScore($domainPoints['C'], $domainMaxPoints['C']);
        $scorePsy    = $this->normalizeScore($domainPoints['D'], $domainMaxPoints['D']);

        // === Step 4: Skor total terbobot (sesuai weight di QuestionBankService) ===
        $totalScore = round(
            ($scoreCardio * 0.35) +
            ($scoreMsk    * 0.30) +
            ($scoreAcute  * 0.20) +
            ($scorePsy    * 0.15),
            2
        );

        // === Step 5: Klasifikasi risiko ===
        $riskLabel = $this->classify($totalScore, count($redFlags) > 0, count($yellowFlags));

        // === Step 6: Modifier — faktor mitra (kacamata) ===
        // Kacamata diambil langsung dari jawaban kuesioner terkini (bukan profil lama)
        $isKacamata = (bool) $this->parseBoolean($answers['C4_using_glasses'] ?? false);
        
        $kacamataNote = null;
        if ($isKacamata && $riskLabel === 'low') {
            // Kacamata bukan red flag medis, tapi mitra ingin pemantauan ekstra.
            // Naikkan low → medium agar masuk daftar pemantauan panitia.
            $riskLabel = 'medium';
            $kacamataNote = 'Peserta menggunakan kacamata (flag mitra) — dinaikkan ke MEDIUM untuk pemantauan ekstra dari panitia, terutama untuk cabor kontak.';
            $yellowFlags[] = [
                'code'   => 'MITRA_KACAMATA',
                'text'   => 'Peserta menggunakan kacamata',
                'reason' => 'Flag mitra: berisiko pecah/lepas saat olahraga kontak.',
            ];
        }

        // === Step 7: Modifier — BMI ekstrem ===
        if ($demo['bmi'] !== null) {
            if ($demo['bmi'] < 17 || $demo['bmi'] > 32) {
                $yellowFlags[] = [
                    'code'   => 'BMI_EXTREME',
                    'text'   => 'BMI di luar rentang normal',
                    'reason' => sprintf('BMI tercatat = %.1f kg/m². Pertimbangkan pemantauan tambahan.', $demo['bmi']),
                ];
            }
        }

        // === Step 8: Confidence score (transparansi tingkat keyakinan) ===
        $confidence = $this->calculateConfidence($answers, $questions, $redFlags);

        // === Step 9: Output untuk panitia ===
        $requiresClearance = $riskLabel === 'high';
        $recommendation = $this->buildRecommendation($riskLabel, $redFlags, $yellowFlags, $kacamataNote);
        $panitiaSummary = $this->buildPanitiaSummary(
            $riskLabel, $totalScore, $scoreCardio, $scoreMsk, $scoreAcute, $scorePsy,
            $redFlags, $yellowFlags, $demo, $isKacamata
        );

        // === Step 10: Validity window ===
        $validUntil = Carbon::now()->addMonths(self::VALIDITY_MONTHS);

        return [
            // Snapshot
            'sport_branch_snapshot'  => $player?->sport_branch,
            'age_snapshot'           => $demo['age'],
            'bmi_snapshot'           => $demo['bmi'],
            'is_kacamata_snapshot'   => $isKacamata,

            // Versioning
            'questionnaire_version'  => QuestionBankService::VERSION,
            'algorithm_version'      => self::VERSION,

            // Skor
            'score_cardiovascular'   => $scoreCardio,
            'score_musculoskeletal'  => $scoreMsk,
            'score_acute_readiness'  => $scoreAcute,
            'score_psychosocial'     => $scorePsy,
            'total_score'            => $totalScore,
            'confidence_score'       => $confidence,

            // Detail
            'score_breakdown'        => $breakdown,
            'red_flags'              => $redFlags,
            'yellow_flags'           => $yellowFlags,

            // Klasifikasi
            'risk_label'             => $riskLabel,
            'recommendation'         => $recommendation,
            'panitia_summary'        => $panitiaSummary,
            'requires_clearance'     => $requiresClearance,

            // Jawaban mentah & pengayaan dari open text
            'pain_score'             => (int) ($answers['B3_pain_score'] ?? 0),
            'injury_history'         => $answers['B7_injury_history_description'] ?? null,
            'injury_location'        => $answers['B6_recurring_injury_area'] ?? null,
            'current_condition'      => $answers['D4_additional_notes'] ?? null,
            'form_responses'         => $answers,

            // Validity
            'valid_until'            => $validUntil,
        ];
    }

    // ====================================================================
    // CORE: scoring satu pertanyaan
    // ====================================================================

    private function scoreOneQuestion(array $q, mixed $answer): array
    {
        $type = $q['type'];
        $weight = $q['weight'] ?? 0;
        $isRedFlag = false;
        $isYellowFlag = false;
        $points = 0;
        $maxPoints = 0;
        $reason = '';

        switch ($type) {
            case 'boolean':
                $val = $this->parseBoolean($answer);
                $maxPoints = $weight;
                if ($val === true) {
                    $points = $weight;
                    if ($q['is_red_flag'] ?? false) {
                        $isRedFlag = true;
                        $reason = 'Jawaban "Ya" pada pertanyaan red flag.';
                    } elseif ($q['is_yellow_flag'] ?? false) {
                        $isYellowFlag = true;
                        $reason = 'Jawaban "Ya" pada pertanyaan yellow flag.';
                    } else {
                        $reason = 'Jawaban "Ya".';
                    }
                } else {
                    $reason = 'Jawaban "Tidak".';
                }
                break;

            case 'scale':
                $min = $q['min'] ?? 0;
                $max = $q['max'] ?? 10;
                $val = max($min, min($max, (int) ($answer ?? $min)));
                // Invert untuk subjective_fitness (C2): nilai tinggi = baik
                if (($q['code'] ?? '') === 'C2_subjective_fitness') {
                    $effective = ($max + 1) - $val; // 1->10, 10->1
                    $points = $effective * $weight;
                    $maxPoints = $max * $weight;
                    $reason = "Skala kondisi: $val/$max (semakin rendah = semakin berisiko).";
                } else {
                    // Default: nilai tinggi = lebih berisiko (mis. pain score)
                    $points = $val * $weight;
                    $maxPoints = $max * $weight;
                    $reason = "Skala: $val/$max.";
                    // Pain score: >=7 red, 4-6 yellow
                    if (($q['code'] ?? '') === 'B3_pain_score') {
                        if ($val >= 7) {
                            $isRedFlag = true;
                            $reason = "Skala nyeri $val/10 (>=7) — red flag.";
                        } elseif ($val >= 4) {
                            $isYellowFlag = true;
                            $reason = "Skala nyeri $val/10 (4-6) — yellow flag.";
                        }
                    }
                    // Stres D1: >=4 yellow flag
                    if (($q['code'] ?? '') === 'D1_stress_level' && $val >= 4) {
                        $isYellowFlag = true;
                        $reason = "Tingkat stres $val/5 — yellow flag.";
                    }
                }
                break;

            case 'single_choice':
                $opts = $q['options'] ?? [];
                $maxPoints = 0;
                foreach ($opts as $o) {
                    $maxPoints = max($maxPoints, ($o['points'] ?? 0));
                }
                $selected = collect($opts)->firstWhere('value', $answer);
                if ($selected) {
                    $points = $selected['points'] ?? 0;
                    $reason = 'Pilihan: ' . $selected['label'];
                    // Khusus B4_injury_count_12months: "3+" = yellow flag
                    if (($q['code'] ?? '') === 'B4_injury_count_12months' && $answer === '3+') {
                        $isYellowFlag = true;
                        $reason .= ' — yellow flag (cedera berulang).';
                    }
                    // Khusus D2_sleep_hours: "<5" = yellow flag
                    if (($q['code'] ?? '') === 'D2_sleep_hours' && $answer === '<5') {
                        $isYellowFlag = true;
                        $reason .= ' — yellow flag (kurang tidur kronis).';
                    }
                }
                break;

            case 'multi_choice':
                $opts = $q['options'] ?? [];
                $maxPoints = collect($opts)
                    ->where('value', '!=', 'none')
                    ->sum('points');
                $selectedValues = is_array($answer) ? $answer : (array) $answer;
                $selectedLabels = [];
                foreach ($selectedValues as $v) {
                    $o = collect($opts)->firstWhere('value', $v);
                    if ($o) {
                        $points += $o['points'] ?? 0;
                        $selectedLabels[] = $o['label'];
                    }
                }
                $reason = $selectedLabels
                    ? ('Dipilih: ' . implode(', ', $selectedLabels))
                    : 'Tidak ada keluhan.';
                // Cedera baru = yellow flag
                if (in_array('new_injury', $selectedValues, true)) {
                    $isYellowFlag = true;
                    $reason .= ' — yellow flag (cedera baru 7 hari terakhir).';
                }
                break;

            case 'open_text':
                $points = $this->scoreOpenText($q['code'] ?? '', (string) ($answer ?? ''), $weight);
                $maxPoints = $weight;
                if ($points > 0) {
                    $isYellowFlag = true;
                    $reason = 'Deteksi kata kunci risiko pada jawaban terbuka.';
                } else {
                    $reason = 'Tidak ada kata kunci risiko terdeteksi.';
                }
                break;

            case 'number':
                // Tidak dihitung sebagai skor langsung (dipakai untuk snapshot).
                $reason = 'Data numerik (snapshot demografi).';
                break;
        }

        return [
            'type'           => $type,
            'answer'         => $answer,
            'points'         => $points,
            'max_points'     => $maxPoints,
            'is_red_flag'    => $isRedFlag,
            'is_yellow_flag' => $isYellowFlag,
            'reason'         => $reason,
        ];
    }

    /**
     * Rule-based keyword detector untuk pertanyaan terbuka.
     * Dapat di-extend dengan LLM call (opsional, di luar service ini).
     */
    private function scoreOpenText(string $code, string $text, int $weight): int
    {
        $text = mb_strtolower(trim($text));

        // Anggap "tidak" / "tidak ada" / kosong = 0 poin
        if ($text === '' || in_array($text, ['tidak', 'tidak ada', 'tidak ada.', '-', 'none', 'no'], true)) {
            return 0;
        }

        // Keyword berdasarkan jenis pertanyaan
        $keywords = match ($code) {
            'A7_current_medication' => [
                'beta blocker', 'beta-blocker', 'insulin', 'antikoagulan', 'warfarin',
                'antihipertensi', 'kortikosteroid', 'inhaler', 'epilepsi', 'antikejang',
                'jantung', 'tekanan darah',
            ],
            'B6_recurring_injury_area', 'B7_injury_history_description' => [
                'acl', 'meniscus', 'meniskus', 'patah', 'fraktur', 'dislokasi',
                'robekan', 'robek', 'cedera berat', 'operasi', 'pasca operasi',
                'kambuh', 'tidak stabil', 'sering nyeri',
            ],
            'D4_additional_notes' => [
                'pingsan', 'sesak', 'nyeri dada', 'sakit', 'cedera', 'kambuh',
                'tidak fit', 'tidak siap', 'demam', 'flu',
            ],
            default => [],
        };

        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return $weight;
            }
        }

        return 0;
    }

    // ====================================================================
    // HELPERS
    // ====================================================================

    private function parseBoolean(mixed $v): ?bool
    {
        if (is_bool($v)) return $v;
        if (is_null($v)) return null;
        if (is_string($v)) {
            $v = strtolower(trim($v));
            return in_array($v, ['1', 'true', 'ya', 'yes', 'y'], true);
        }
        if (is_numeric($v)) return (int) $v === 1;
        return null;
    }

    private function normalizeScore(float $points, float $max): float
    {
        if ($max <= 0) return 0;
        return round(min(100, ($points / $max) * 100), 2);
    }

    private function classify(float $totalScore, bool $hasRedFlag, int $yellowFlagCount = 0): string
    {
        // Red flag = otomatis HIGH (override skor).
        if ($hasRedFlag) return 'high';

        // Banyak yellow flags = minimal MEDIUM (escalation rule).
        // Logikanya: meski skor numerik rendah, akumulasi banyak sinyal kekhawatiran
        // tetap perlu perhatian panitia.
        if ($yellowFlagCount >= self::YELLOW_FLAG_ESCALATION_THRESHOLD && $totalScore <= self::THRESHOLD_LOW_MAX) {
            return 'medium';
        }

        if ($totalScore <= self::THRESHOLD_LOW_MAX) return 'low';
        if ($totalScore <= self::THRESHOLD_MEDIUM_MAX) return 'medium';
        return 'high';
    }

    private function extractDemographics(array $answers): array
    {
        $height = (float) ($answers['demo_height_cm'] ?? 0);
        $weight = (float) ($answers['demo_weight_kg'] ?? 0);
        $age = isset($answers['demo_age']) ? (int) $answers['demo_age'] : null;

        $bmi = null;
        if ($height > 0 && $weight > 0) {
            $bmi = round($weight / (($height / 100) ** 2), 2);
        }

        return [
            'age'    => $age,
            'height' => $height ?: null,
            'weight' => $weight ?: null,
            'bmi'    => $bmi,
        ];
    }

    /**
     * Confidence score: seberapa "yakin" sistem dengan hasilnya.
     * Faktor: kelengkapan jawaban + kekuatan sinyal (red flag = lebih yakin).
     */
    private function calculateConfidence(array $answers, array $questions, array $redFlags): float
    {
        $required = array_filter($questions, fn($q) => ($q['required'] ?? false));
        $total = count($required);
        $answered = 0;
        foreach ($required as $code => $q) {
            $a = $answers[$code] ?? null;
            if ($a !== null && $a !== '' && $a !== []) $answered++;
        }
        $completeness = $total > 0 ? ($answered / $total) : 1.0;

        // Red flag memberikan confidence boost (sinyal kuat & jelas)
        $signalBoost = count($redFlags) > 0 ? 5 : 0;

        return round(min(99.9, ($completeness * 90) + $signalBoost + 5), 1);
    }

    private function buildRecommendation(
        string $riskLabel,
        array $redFlags,
        array $yellowFlags,
        ?string $kacamataNote
    ): string {
        $parts = [];

        switch ($riskLabel) {
            case 'high':
                $parts[] = 'REKOMENDASI: Peserta tidak diperkenankan bertanding sebelum mendapatkan clearance medis dari dokter atau fisioterapis.';
                if (count($redFlags) > 0) {
                    $parts[] = 'Red flag terdeteksi: ' . count($redFlags) . ' item (lihat detail).';
                }
                break;
            case 'medium':
                $parts[] = 'REKOMENDASI: Peserta diperbolehkan bertanding dengan pemantauan khusus oleh panitia. Pastikan pemanasan adekuat dan tenaga medis stand-by.';
                if (count($yellowFlags) > 0) {
                    $parts[] = 'Yellow flag terdeteksi: ' . count($yellowFlags) . ' item.';
                }
                break;
            case 'low':
            default:
                $parts[] = 'REKOMENDASI: Peserta dinilai fit untuk bertanding dengan pemantauan standar.';
                break;
        }

        if ($kacamataNote) {
            $parts[] = $kacamataNote;
        }

        return implode(' ', $parts);
    }

    private function buildPanitiaSummary(
        string $riskLabel,
        float $totalScore,
        float $cardio,
        float $msk,
        float $acute,
        float $psy,
        array $redFlags,
        array $yellowFlags,
        array $demo,
        bool $isKacamata
    ): string {
        $lines = [];
        $lines[] = sprintf('Tingkat Risiko: %s | Skor Total: %.1f/100', strtoupper($riskLabel), $totalScore);
        $lines[] = sprintf(
            'Breakdown Domain — Kardio: %.1f | Muskuloskeletal: %.1f | Acute: %.1f | Psikososial: %.1f',
            $cardio, $msk, $acute, $psy
        );

        if ($demo['age']) {
            $lines[] = sprintf(
                'Profil: Usia %d th%s%s',
                $demo['age'],
                $demo['bmi'] ? sprintf(', BMI %.1f', $demo['bmi']) : '',
                $isKacamata ? ', Pengguna kacamata' : ''
            );
        }

        if (count($redFlags) > 0) {
            $lines[] = 'RED FLAGS:';
            foreach ($redFlags as $rf) {
                $lines[] = '  • ' . $rf['code'] . ': ' . $rf['reason'];
            }
        }
        if (count($yellowFlags) > 0) {
            $lines[] = 'YELLOW FLAGS:';
            foreach ($yellowFlags as $yf) {
                $lines[] = '  • ' . $yf['code'] . ': ' . $yf['reason'];
            }
        }
        if (empty($redFlags) && empty($yellowFlags)) {
            $lines[] = 'Tidak ada flag terdeteksi. Pemantauan standar.';
        }

        return implode("\n", $lines);
    }
}