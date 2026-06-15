<?php

namespace App\Services\SelfAssessment;

/**
 * QuestionBankService — Sumber Kebenaran (Single Source of Truth) untuk
 * seluruh daftar pertanyaan self-assessment, beserta metadata scoring-nya.
 *
 * Mengapa di-service?
 *   1. Frontend cukup memanggil 1 endpoint untuk dapat seluruh struktur form.
 *   2. ScoringService menggunakan metadata yang sama → konsistensi terjamin.
 *   3. Update pertanyaan hanya 1 tempat → tidak ada drift.
 *   4. Versioning kuesioner ter-track (untuk audit & reproducibility).
 *
 * LANDASAN ILMIAH (singkat):
 *   - Domain A (Kardiovaskular & Medis): adaptasi PAR-Q+ 2024 oleh
 *     Warburton et al., plus AHA/ACC 14-point pre-participation screening.
 *   - Domain B (Muskuloskeletal): literatur menunjukkan riwayat cedera
 *     meningkatkan risiko cedera baru (OR ≈ 3.17 untuk acute,
 *     OR ≈ 4.31 untuk overuse) — Klemp et al., 2024.
 *   - Domain C (Acute Readiness): adaptasi ACSM pre-exercise checklist.
 *   - Domain D (Psikososial): Ivarsson et al. — stres hidup negatif
 *     dan kelelahan adalah prediktor signifikan cedera olahraga.
 *
 * Struktur tiap pertanyaan:
 *   - code:        ID unik & stabil (dipakai di form_responses)
 *   - domain:      A | B | C | D
 *   - type:        boolean | scale | single_choice | multi_choice | open_text | number
 *   - text:        Teks pertanyaan
 *   - options:     Pilihan jawaban (untuk choice types)
 *   - is_red_flag: True jika jawaban "memicu" red flag (lihat scoring rules)
 *   - weight:      Bobot poin (positif = menambah risiko)
 *   - required:    Wajib diisi atau tidak
 *   - notes:       Catatan teknis untuk developer
 */
class QuestionBankService
{
    /**
     * Versi kuesioner aktif. WAJIB di-bump kalau pertanyaan/scoring diubah.
     */
    public const VERSION = '1.0.0';

    /**
     * Mendapatkan seluruh daftar pertanyaan terstruktur untuk frontend.
     */
    public function getQuestionnaire(): array
    {
        return [
            'version' => self::VERSION,
            'language' => 'id',
            'estimated_duration_minutes' => 7,
            'disclaimer' => $this->getDisclaimer(),
            'sections' => [
                $this->buildSection0Demographics(),
                $this->buildSectionACardiovascular(),
                $this->buildSectionBMusculoskeletal(),
                $this->buildSectionCAcuteReadiness(),
                $this->buildSectionDPsychosocial(),
            ],
            'risk_levels' => [
                'low'    => 'Risiko rendah. Pemantauan standar.',
                'medium' => 'Risiko sedang. Memerlukan pemantauan khusus dari panitia.',
                'high'   => 'Risiko tinggi. Wajib clearance medis sebelum bertanding.',
            ],
        ];
    }

    /**
     * Mendapatkan flat list semua pertanyaan dengan metadata scoring.
     * Dipakai oleh ScoringService.
     */
    public function getAllQuestionsFlat(): array
    {
        $flat = [];
        foreach ($this->getQuestionnaire()['sections'] as $section) {
            foreach ($section['questions'] ?? [] as $q) {
                $flat[$q['code']] = array_merge($q, ['domain' => $section['domain']]);
            }
        }
        return $flat;
    }

    private function getDisclaimer(): string
    {
        return 'Self-assessment ini BUKAN diagnosa medis. Hasil hanya digunakan ' .
               'panitia Telucup untuk memberikan dukungan & pemantauan yang sesuai. ' .
               'Jawablah dengan jujur — informasi yang akurat melindungi Anda dan ' .
               'peserta lain. Untuk evaluasi menyeluruh, konsultasikan dengan dokter. ' .
               'Data Anda dijaga kerahasiaannya sesuai UU Pelindungan Data Pribadi.';
    }

    // ====================================================================
    // BAGIAN 0: DATA DEMOGRAFIS (KONTEKS)
    // ====================================================================
    private function buildSection0Demographics(): array
    {
        return [
            'domain' => 'DEMO',
            'title'  => 'Data Diri',
            'description' => 'Informasi dasar untuk konteks asesmen.',
            'questions' => [
                [
                    'code' => 'demo_age',
                    'type' => 'number',
                    'text' => 'Usia Anda (tahun)',
                    'min'  => 10,
                    'max'  => 80,
                    'required' => true,
                    'weight' => 0,
                ],
                [
                    'code' => 'demo_height_cm',
                    'type' => 'number',
                    'text' => 'Tinggi badan (cm)',
                    'min'  => 100,
                    'max'  => 230,
                    'required' => true,
                    'weight' => 0,
                ],
                [
                    'code' => 'demo_weight_kg',
                    'type' => 'number',
                    'text' => 'Berat badan (kg)',
                    'min'  => 25,
                    'max'  => 200,
                    'required' => true,
                    'weight' => 0,
                ],
                [
                    'code' => 'demo_activity_level',
                    'type' => 'single_choice',
                    'text' => 'Frekuensi olahraga rutin Anda saat ini',
                    'options' => [
                        ['value' => 'sedentary',  'label' => 'Hampir tidak pernah olahraga'],
                        ['value' => 'light',      'label' => '1-2 kali seminggu'],
                        ['value' => 'moderate',   'label' => '3-4 kali seminggu'],
                        ['value' => 'active',     'label' => '5 kali atau lebih per minggu'],
                    ],
                    'required' => true,
                    'weight' => 0,
                ],
            ],
        ];
    }

    // ====================================================================
    // BAGIAN A: KARDIOVASKULAR & KONDISI MEDIS UMUM
    // Adaptasi PAR-Q+ 2024 (Warburton et al.) + AHA/ACC 14-point Screening
    // ====================================================================
    private function buildSectionACardiovascular(): array
    {
        return [
            'domain' => 'A',
            'title'  => 'Kardiovaskular & Kondisi Medis',
            'description' => 'Pertanyaan ini diadaptasi dari PAR-Q+ (standar ' .
                             'internasional pre-participation screening) dan AHA ' .
                             '14-point screening untuk atlet kompetitif.',
            'weight' => 0.35, // 35% dari total skor
            'questions' => [
                [
                    'code' => 'A1_heart_condition_diagnosed',
                    'type' => 'boolean',
                    'text' => 'Apakah dokter pernah menyatakan bahwa Anda memiliki kondisi jantung dan menyarankan untuk hanya melakukan aktivitas fisik dengan rekomendasi medis?',
                    'is_red_flag' => true,
                    'weight' => 25,
                    'required' => true,
                ],
                [
                    'code' => 'A2_chest_pain_during_exercise',
                    'type' => 'boolean',
                    'text' => 'Dalam 12 bulan terakhir, apakah Anda pernah merasakan nyeri dada saat berolahraga?',
                    'is_red_flag' => true,
                    'weight' => 25,
                    'required' => true,
                ],
                [
                    'code' => 'A3_unexplained_fainting',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda pernah pingsan atau hampir pingsan tanpa sebab yang jelas, terutama saat atau setelah aktivitas fisik?',
                    'is_red_flag' => true,
                    'weight' => 25,
                    'required' => true,
                ],
                [
                    'code' => 'A4_serious_medical_condition',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda memiliki kondisi medis serius yang sedang dalam pengobatan? (mis. asma berat tidak terkontrol, epilepsi, diabetes tidak terkontrol, hipertensi tidak terkontrol)',
                    'is_red_flag' => true,
                    'weight' => 20,
                    'required' => true,
                ],
                [
                    'code' => 'A5_family_cardiac_death',
                    'type' => 'boolean',
                    'text' => 'Apakah ada anggota keluarga kandung (orang tua/saudara) yang meninggal mendadak akibat masalah jantung di usia di bawah 50 tahun, atau memiliki penyakit jantung bawaan?',
                    'is_red_flag' => true,
                    'weight' => 20,
                    'required' => true,
                ],
                [
                    'code' => 'A6_breathing_palpitations',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda pernah merasa sesak napas berlebihan, jantung berdebar tidak normal, atau pusing saat berolahraga ringan hingga sedang?',
                    'is_yellow_flag' => true,
                    'weight' => 10,
                    'required' => true,
                ],
                [
                    'code' => 'A7_current_medication',
                    'type' => 'open_text',
                    'text' => 'Apakah Anda saat ini mengonsumsi obat-obatan rutin? Jika ya, sebutkan nama atau jenisnya. Tulis "tidak" jika tidak ada.',
                    'weight' => 5,
                    'required' => true,
                    'notes' => 'Dianalisis sebagai yellow flag jika berisi keyword tertentu (lihat ScoringService).',
                ],
                [
                    'code' => 'A8_severe_allergy',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda memiliki alergi berat atau riwayat anafilaksis (alergi yang mengancam jiwa)?',
                    'is_yellow_flag' => true,
                    'weight' => 8,
                    'required' => true,
                ],
            ],
        ];
    }

    // ====================================================================
    // BAGIAN B: RIWAYAT CEDERA & MUSKULOSKELETAL
    // Berbasis literatur: previous injury = predictor kuat injury baru
    // ====================================================================
    private function buildSectionBMusculoskeletal(): array
    {
        return [
            'domain' => 'B',
            'title'  => 'Riwayat Cedera & Muskuloskeletal',
            'description' => 'Berbasis temuan bahwa cedera sebelumnya meningkatkan ' .
                             'risiko cedera baru hingga 3 kali lipat (Klemp et al., 2024).',
            'weight' => 0.30, // 30%
            'questions' => [
                [
                    'code' => 'B1_currently_recovering',
                    'type' => 'boolean',
                    'text' => 'Apakah saat ini Anda sedang dalam masa pemulihan cedera yang belum dinyatakan sembuh oleh tenaga medis? (mis. patah tulang, robekan ligamen, cedera ACL/meniscus)',
                    'is_red_flag' => true,
                    'weight' => 25,
                    'required' => true,
                ],
                [
                    'code' => 'B2_current_pain_worsens',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda merasakan nyeri di tulang, sendi, atau otot SAAT INI yang memburuk dengan aktivitas fisik?',
                    'is_red_flag' => true,
                    'weight' => 20,
                    'required' => true,
                ],
                [
                    'code' => 'B3_pain_score',
                    'type' => 'scale',
                    'text' => 'Pada skala 0-10, seberapa intens nyeri yang Anda rasakan saat ini? (0 = tidak ada, 10 = nyeri sangat parah)',
                    'min' => 0,
                    'max' => 10,
                    'weight' => 3, // dikalikan nilai skala
                    'required' => true,
                    'notes' => 'Skor >=7 = red flag, 4-6 = yellow flag (dihandle ScoringService).',
                ],
                [
                    'code' => 'B4_injury_count_12months',
                    'type' => 'single_choice',
                    'text' => 'Dalam 12 bulan terakhir, berapa kali Anda mengalami cedera olahraga yang membuat Anda absen latihan/kompetisi lebih dari 1 minggu?',
                    'options' => [
                        ['value' => '0', 'label' => 'Tidak pernah', 'points' => 0],
                        ['value' => '1', 'label' => '1 kali',       'points' => 5],
                        ['value' => '2', 'label' => '2 kali',       'points' => 10],
                        ['value' => '3+', 'label' => '3 kali atau lebih', 'points' => 15],
                    ],
                    'required' => true,
                    'weight' => 1, // langsung ambil dari points
                    'notes' => '3+ kali = yellow flag.',
                ],
                [
                    'code' => 'B5_orthopedic_surgery',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda pernah menjalani operasi orthopedic (sendi, tulang, ligamen, atau tendon) dalam 2 tahun terakhir?',
                    'is_yellow_flag' => true,
                    'weight' => 12,
                    'required' => true,
                ],
                [
                    'code' => 'B6_recurring_injury_area',
                    'type' => 'open_text',
                    'text' => 'Apakah ada bagian tubuh yang sering "kambuh" atau terasa tidak stabil saat olahraga? Sebutkan bagiannya. Tulis "tidak" jika tidak ada.',
                    'weight' => 5,
                    'required' => true,
                    'notes' => 'LLM (jika tersedia) akan memetakan ke area tubuh; rule-based fallback memakai keyword.',
                ],
                [
                    'code' => 'B7_injury_history_description',
                    'type' => 'open_text',
                    'text' => 'Jelaskan secara singkat riwayat cedera Anda yang paling signifikan (jika ada). Tulis "tidak ada" jika tidak pernah cedera serius.',
                    'weight' => 0, // tidak dihitung skor — info kontekstual untuk panitia
                    'required' => false,
                ],
            ],
        ];
    }

    // ====================================================================
    // BAGIAN C: ACUTE READINESS (KONDISI 7 HARI TERAKHIR)
    // ====================================================================
    private function buildSectionCAcuteReadiness(): array
    {
        return [
            'domain' => 'C',
            'title'  => 'Kondisi Saat Ini',
            'description' => 'Memantau kondisi akut yang dapat mempengaruhi performa & risiko cedera.',
            'weight' => 0.20, // 20%
            'questions' => [
                [
                    'code' => 'C1_acute_symptoms',
                    'type' => 'multi_choice',
                    'text' => 'Dalam 7 hari terakhir, apakah Anda mengalami salah satu kondisi berikut? (boleh pilih lebih dari satu)',
                    'options' => [
                        ['value' => 'fever_flu',    'label' => 'Demam, flu, atau batuk',                  'points' => 8],
                        ['value' => 'diarrhea',     'label' => 'Diare atau dehidrasi',                    'points' => 6],
                        ['value' => 'new_injury',   'label' => 'Cedera baru (memar, terkilir, dll)',      'points' => 10],
                        ['value' => 'sleep_short',  'label' => 'Kurang tidur kronis (<5 jam/malam)',      'points' => 5],
                        ['value' => 'none',         'label' => 'Tidak ada keluhan',                        'points' => 0],
                    ],
                    'required' => true,
                    'weight' => 1,
                ],
                [
                    'code' => 'C2_subjective_fitness',
                    'type' => 'scale',
                    'text' => 'Pada skala 1-10, bagaimana kondisi fisik Anda saat ini dibandingkan kondisi normal Anda? (1 = sangat buruk, 10 = prima)',
                    'min' => 1,
                    'max' => 10,
                    'weight' => 2, // dihitung terbalik: 11 - nilai
                    'required' => true,
                ],
                [
                    'code' => 'C3_preparation_level',
                    'type' => 'single_choice',
                    'text' => 'Seberapa siap Anda secara latihan untuk cabang olahraga yang akan diikuti?',
                    'options' => [
                        ['value' => 'well_prepared',  'label' => 'Sudah berlatih rutin lebih dari 1 bulan', 'points' => 0],
                        ['value' => 'sporadic',       'label' => 'Latihan sporadis (tidak rutin)',          'points' => 5],
                        ['value' => 'general_fitness','label' => 'Belum latihan khusus, mengandalkan kebugaran umum', 'points' => 10],
                        ['value' => 'not_prepared',   'label' => 'Sama sekali belum siap',                  'points' => 15],
                    ],
                    'required' => true,
                    'weight' => 1,
                ],
                [
                    'code' => 'C4_using_glasses',
                    'type' => 'boolean',
                    'text' => 'Apakah Anda menggunakan kacamata dalam aktivitas sehari-hari?',
                    'is_yellow_flag' => false,
                    'weight' => 0, // Dihitung sebagai modifier di ScoringService
                    'required' => true,
                ],
            ],
        ];
    }

    // ====================================================================
    // BAGIAN D: PSIKOSOSIAL & LIFESTYLE
    // Berbasis Ivarsson et al. — stres = prediktor cedera olahraga
    // ====================================================================
    private function buildSectionDPsychosocial(): array
    {
        return [
            'domain' => 'D',
            'title'  => 'Psikososial & Lifestyle',
            'description' => 'Faktor stres dan gaya hidup yang berkorelasi dengan risiko cedera.',
            'weight' => 0.15, // 15%
            'questions' => [
                [
                    'code' => 'D1_stress_level',
                    'type' => 'scale',
                    'text' => 'Dalam 1 bulan terakhir, seberapa sering Anda merasa stres berat akibat akademik atau personal? (1 = tidak pernah, 5 = sangat sering)',
                    'min' => 1,
                    'max' => 5,
                    'weight' => 3, // 1=0pts, 5=12pts
                    'required' => true,
                ],
                [
                    'code' => 'D2_sleep_hours',
                    'type' => 'single_choice',
                    'text' => 'Berapa rata-rata jam tidur Anda dalam 2 minggu terakhir?',
                    'options' => [
                        ['value' => '<5',  'label' => 'Kurang dari 5 jam',  'points' => 12],
                        ['value' => '5-6', 'label' => '5-6 jam',            'points' => 6],
                        ['value' => '7-8', 'label' => '7-8 jam',            'points' => 0],
                        ['value' => '>8',  'label' => 'Lebih dari 8 jam',   'points' => 2],
                    ],
                    'required' => true,
                    'weight' => 1,
                    'notes' => '<5 jam = yellow flag.',
                ],
                [
                    'code' => 'D3_smoking_alcohol',
                    'type' => 'single_choice',
                    'text' => 'Apakah Anda merokok atau mengonsumsi alkohol secara rutin? (Informasi ini hanya untuk keperluan medis darurat dan kerahasiaan dijaga)',
                    'options' => [
                        ['value' => 'none',     'label' => 'Tidak keduanya',     'points' => 0],
                        ['value' => 'social',   'label' => 'Sosial/jarang',      'points' => 3],
                        ['value' => 'regular',  'label' => 'Rutin/sering',       'points' => 8],
                    ],
                    'required' => true,
                    'weight' => 1,
                ],
                [
                    'code' => 'D4_additional_notes',
                    'type' => 'open_text',
                    'text' => 'Apakah ada hal lain terkait kondisi kesehatan atau fisik Anda yang perlu diketahui panitia? (opsional)',
                    'weight' => 0,
                    'required' => false,
                    'notes' => 'Dianalisis LLM (jika tersedia) untuk menangkap red flag yang mungkin terlewat.',
                ],
            ],
        ];
    }
}
