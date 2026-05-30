<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewSelfAssessmentRequest;
use App\Http\Requests\StoreSelfAssessmentRequest;
use App\Models\Player;
use App\Models\SelfAssessment;
use App\Services\SelfAssessment\QuestionBankService;
use App\Services\SelfAssessment\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SelfAssessmentResult",
    title: "Self Assessment Result",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "player_id", type: "integer"),
        new OA\Property(property: "player_name", type: "string", nullable: true),
        new OA\Property(property: "sport_branch", type: "string", nullable: true),
        new OA\Property(property: "contingent", type: "string", nullable: true),
        new OA\Property(
            property: "snapshot",
            type: "object",
            properties: [
                new OA\Property(property: "age", type: "integer"),
                new OA\Property(property: "bmi", type: "number", format: "float"),
                new OA\Property(property: "is_kacamata", type: "boolean")
            ]
        ),
        new OA\Property(property: "risk_label", type: "string", enum: ["low", "medium", "high"]),
        new OA\Property(property: "total_score", type: "number", format: "float"),
        new OA\Property(property: "confidence_score", type: "number", format: "float"),
        new OA\Property(property: "requires_clearance", type: "boolean"),
        new OA\Property(
            property: "domain_scores",
            type: "object",
            properties: [
                new OA\Property(property: "cardiovascular", type: "number", format: "float"),
                new OA\Property(property: "musculoskeletal", type: "number", format: "float"),
                new OA\Property(property: "acute_readiness", type: "number", format: "float"),
                new OA\Property(property: "psychosocial", type: "number", format: "float")
            ]
        ),
        new OA\Property(
            property: "red_flags",
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "text", type: "string"),
                    new OA\Property(property: "reason", type: "string")
                ]
            )
        ),
        new OA\Property(
            property: "yellow_flags",
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "text", type: "string"),
                    new OA\Property(property: "reason", type: "string")
                ]
            )
        ),
        new OA\Property(property: "recommendation", type: "string", nullable: true),
        new OA\Property(property: "panitia_summary", type: "string", nullable: true),
        new OA\Property(property: "questionnaire_version", type: "string"),
        new OA\Property(property: "algorithm_version", type: "string"),
        new OA\Property(property: "valid_until", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "is_valid", type: "boolean"),
        new OA\Property(
            property: "medical_review",
            type: "object",
            properties: [
                new OA\Property(property: "medical_notes", type: "string", nullable: true),
                new OA\Property(property: "is_allowed_to_play", type: "boolean", nullable: true),
                new OA\Property(property: "pic_confirmed", type: "boolean"),
                new OA\Property(property: "reviewed_at", type: "string", format: "date-time", nullable: true)
            ]
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class SelfAssessmentController extends Controller
{
    public function __construct(
        private readonly QuestionBankService $questionBank,
        private readonly ScoringService $scoringService
    ) {}

    #[OA\Get(
        path: "/api/self-assessment/questionnaire",
        operationId: "getSelfAssessmentQuestionnaire",
        summary: "Mendapatkan struktur kuesioner self-assessment",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"],
        description: "Mengembalikan daftar pertanyaan terstruktur dalam 4 domain yang harus dirender oleh frontend. Response berisi version, sections (array berisi section DEMO, A, B, C, D), dan masing-masing section berisi questions dengan field code, type, text, options, is_red_flag, weight, required."
    )]
    #[OA\Response(
        response: 200,
        description: "Struktur kuesioner lengkap",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   type: "object",
                    properties: [
                        new OA\Property(property: "version", type: "string", example: "1.0.0"),
                        new OA\Property(property: "sections", type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "code",        type: "string",  example: "A"),
                                    new OA\Property(property: "title",       type: "string",  example: "Riwayat Kardiovaskular"),
                                    new OA\Property(property: "description", type: "string",  nullable: true),
                                    new OA\Property(property: "questions",   type: "array",
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: "code",       type: "string",  example: "A1_heart_condition_diagnosed"),
                                                new OA\Property(property: "type",       type: "string",  enum: ["boolean", "integer", "select"], example: "boolean"),
                                                new OA\Property(property: "text",       type: "string",  example: "Apakah Anda pernah didiagnosis penyakit jantung?"),
                                                new OA\Property(property: "options",    type: "array",   nullable: true,
                                                    items: new OA\Items(type: "string")),
                                                new OA\Property(property: "is_red_flag",type: "boolean", example: true),
                                                new OA\Property(property: "weight",     type: "number",  format: "float", example: 10.0),
                                                new OA\Property(property: "required",   type: "boolean", example: true),
                                            ]
                                        )
                                    ),
                                ]
                            )
                        ),
                    ]
                ),
            ]
        )
    )]
    public function questionnaire(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->questionBank->getQuestionnaire(),
        ]);
    }

    #[OA\Post(
        path: "/api/self-assessment",
        operationId: "storeSelfAssessment",
        summary: "Submit jawaban self-assessment dan dapatkan klasifikasi risiko",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"],
        description: "Menerima jawaban kuesioner dalam format key-value (answers), menghitung skor per domain (kardiovaskular 35%, muskuloskeletal 30%, acute readiness 20%, psikososial 15%), mendeteksi red flag dan yellow flag, lalu mengklasifikasikan risiko ke LOW / MEDIUM / HIGH. Algoritma berbasis PAR-Q+ 2024 dan AHA 14-point screening. Field yang wajib divalidasi server: demo_age, demo_height_cm, demo_weight_kg, demo_activity_level, A1–A5, B1–B3. Selebihnya wajib secara logis untuk scoring lengkap."
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["answers"],
            properties: [
                new OA\Property(
                    property: "player_id",
                    type: "integer",
                    nullable: true,
                    description: "ID player (opsional). Kosongkan agar backend memakai player milik user yang login. Isi jika panitia submit atas nama player lain.",
                    example: null
                ),
                new OA\Property(
                    property: "answers",
                    type: "object",
                    description: "Seluruh jawaban kuesioner dalam format { question_code: value }. Gunakan endpoint GET /api/self-assessment/questionnaire untuk mendapat daftar pertanyaan terbaru.",
                    properties: [
                        // ── DEMO: Data Diri ──────────────────────────────────────────
                        new OA\Property(property: "demo_age",           type: "integer", minimum: 10, maximum: 80,  description: "[WAJIB] Usia (tahun)"),
                        new OA\Property(property: "demo_height_cm",     type: "number",  minimum: 100, maximum: 230, description: "[WAJIB] Tinggi badan (cm)"),
                        new OA\Property(property: "demo_weight_kg",     type: "number",  minimum: 25,  maximum: 200, description: "[WAJIB] Berat badan (kg)"),
                        new OA\Property(property: "demo_activity_level",type: "string",  enum: ["sedentary", "light", "moderate", "active"], description: "[WAJIB] Frekuensi olahraga rutin saat ini"),

                        // ── Bagian A: Kardiovaskular & Kondisi Medis ─────────────────
                        new OA\Property(property: "A1_heart_condition_diagnosed",   type: "boolean", description: "[WAJIB · RED FLAG] Pernah didiagnosis kondisi jantung + disarankan dokter hanya olahraga dengan rekomendasi medis"),
                        new OA\Property(property: "A2_chest_pain_during_exercise",  type: "boolean", description: "[WAJIB · RED FLAG] Nyeri dada saat olahraga dalam 12 bulan terakhir"),
                        new OA\Property(property: "A3_unexplained_fainting",        type: "boolean", description: "[WAJIB · RED FLAG] Pingsan / hampir pingsan tanpa sebab jelas (terutama saat/setelah aktivitas)"),
                        new OA\Property(property: "A4_serious_medical_condition",   type: "boolean", description: "[WAJIB · RED FLAG] Kondisi medis serius dalam pengobatan (asma berat, epilepsi, diabetes, hipertensi tidak terkontrol)"),
                        new OA\Property(property: "A5_family_cardiac_death",        type: "boolean", description: "[WAJIB · RED FLAG] Keluarga kandung meninggal mendadak karena jantung sebelum usia 50 tahun"),
                        new OA\Property(property: "A6_breathing_palpitations",      type: "boolean", description: "[WAJIB · YELLOW FLAG] Sesak napas berlebihan / jantung berdebar / pusing saat olahraga ringan–sedang"),
                        new OA\Property(property: "A7_current_medication",          type: "string",  description: "[WAJIB] Obat-obatan rutin saat ini. Tulis 'tidak' jika tidak ada."),
                        new OA\Property(property: "A8_severe_allergy",              type: "boolean", description: "[WAJIB · YELLOW FLAG] Riwayat alergi berat / anafilaksis"),

                        // ── Bagian B: Riwayat Cedera & Muskuloskeletal ───────────────
                        new OA\Property(property: "B1_currently_recovering",     type: "boolean", description: "[WAJIB · RED FLAG] Sedang dalam masa pemulihan cedera belum dinyatakan sembuh (patah tulang, robekan ligamen, ACL/meniscus)"),
                        new OA\Property(property: "B2_current_pain_worsens",     type: "boolean", description: "[WAJIB · RED FLAG] Nyeri tulang/sendi/otot saat ini yang memburuk dengan aktivitas"),
                        new OA\Property(property: "B3_pain_score",               type: "integer", minimum: 0, maximum: 10, description: "[WAJIB] Intensitas nyeri saat ini (0=tidak ada, 10=sangat parah). ≥7 = red flag, 4–6 = yellow flag."),
                        new OA\Property(property: "B4_injury_count_12months",    type: "string",  enum: ["0", "1", "2", "3+"], description: "[WAJIB] Jumlah cedera olahraga dalam 12 bln yang menyebabkan absen >1 minggu. '3+' = yellow flag."),
                        new OA\Property(property: "B5_orthopedic_surgery",       type: "boolean", description: "[WAJIB · YELLOW FLAG] Operasi orthopedic (sendi/tulang/ligamen/tendon) dalam 2 tahun terakhir"),
                        new OA\Property(property: "B6_recurring_injury_area",    type: "string",  description: "[WAJIB] Bagian tubuh yang sering kambuh/tidak stabil. Tulis 'tidak' jika tidak ada."),
                        new OA\Property(property: "B7_injury_history_description", type: "string", nullable: true, description: "[OPSIONAL] Deskripsi singkat riwayat cedera paling signifikan. Tulis 'tidak ada' jika tidak pernah."),

                        // ── Bagian C: Kondisi Saat Ini (7 hari terakhir) ────────────
                        new OA\Property(
                            property: "C1_acute_symptoms",
                            type: "array",
                            items: new OA\Items(type: "string", enum: ["fever_flu", "diarrhea", "new_injury", "sleep_short", "none"]),
                            description: "[WAJIB] Keluhan 7 hari terakhir (multi-pilih). Kirim ['none'] jika tidak ada keluhan. Nilai: fever_flu=demam/flu/batuk, diarrhea=diare/dehidrasi, new_injury=cedera baru, sleep_short=kurang tidur (<5 jam/malam)."
                        ),
                        new OA\Property(property: "C2_subjective_fitness",   type: "integer", minimum: 1, maximum: 10, description: "[WAJIB] Kondisi fisik saat ini vs kondisi normal (1=sangat buruk, 10=prima)"),
                        new OA\Property(property: "C3_preparation_level",    type: "string",  enum: ["well_prepared", "sporadic", "general_fitness", "not_prepared"], description: "[WAJIB] Kesiapan latihan untuk cabang olahraga yang diikuti"),

                        // ── Bagian D: Psikososial & Lifestyle ───────────────────────
                        new OA\Property(property: "D1_stress_level",      type: "integer", minimum: 1, maximum: 5, description: "[WAJIB] Frekuensi stres berat akibat akademik/personal dalam 1 bulan terakhir (1=tidak pernah, 5=sangat sering)"),
                        new OA\Property(property: "D2_sleep_hours",       type: "string",  enum: ["<5", "5-6", "7-8", ">8"], description: "[WAJIB] Rata-rata jam tidur per malam dalam 2 minggu terakhir. '<5' = yellow flag."),
                        new OA\Property(property: "D3_smoking_alcohol",   type: "string",  enum: ["none", "social", "regular"], description: "[WAJIB] Kebiasaan merokok/konsumsi alkohol (none=tidak keduanya, social=jarang, regular=rutin)"),
                        new OA\Property(property: "D4_additional_notes",  type: "string",  nullable: true, description: "[OPSIONAL] Informasi kesehatan/fisik tambahan untuk panitia"),
                    ],
                    example: [
                        "demo_age"                      => 21,
                        "demo_height_cm"                => 170,
                        "demo_weight_kg"                => 65,
                        "demo_activity_level"           => "moderate",
                        "A1_heart_condition_diagnosed"  => false,
                        "A2_chest_pain_during_exercise" => false,
                        "A3_unexplained_fainting"       => false,
                        "A4_serious_medical_condition"  => false,
                        "A5_family_cardiac_death"       => false,
                        "A6_breathing_palpitations"     => false,
                        "A7_current_medication"         => "tidak",
                        "A8_severe_allergy"             => false,
                        "B1_currently_recovering"       => false,
                        "B2_current_pain_worsens"       => false,
                        "B3_pain_score"                 => 2,
                        "B4_injury_count_12months"      => "0",
                        "B5_orthopedic_surgery"         => false,
                        "B6_recurring_injury_area"      => "tidak",
                        "B7_injury_history_description" => "tidak ada",
                        "C1_acute_symptoms"             => ["none"],
                        "C2_subjective_fitness"         => 8,
                        "C3_preparation_level"          => "well_prepared",
                        "D1_stress_level"               => 2,
                        "D2_sleep_hours"                => "7-8",
                        "D3_smoking_alcohol"            => "none",
                        "D4_additional_notes"           => null,
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Assessment berhasil disimpan",
        content: new OA\JsonContent(ref: "#/components/schemas/SelfAssessmentResult")
    )]
    #[OA\Response(response: 403, description: "User mencoba submit atas nama player lain", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Profil player tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function store(StoreSelfAssessmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $playerId = $request->input('player_id');

        $player = null;
        if ($playerId) {
            $player = Player::find($playerId);
            if ($player && !$user->role === 'panitia') {
                if ($player->user_id !== $user->id) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Anda tidak memiliki akses untuk submit assessment atas nama player lain.',
                    ], 403);
                }
            }
        } else {
            $player = $user->player;
        }

        if (!$player) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Profil player tidak ditemukan. Lengkapi profil terlebih dahulu.',
            ], 404);
        }

        $answers = $request->input('answers', []);

        try {
            DB::beginTransaction();

            $payload = $this->scoringService->score($answers, $user, $player);
            $payload['player_id'] = $player->id;
            $assessment = SelfAssessment::create($payload);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Self-assessment berhasil disimpan.',
                'data'    => $this->formatAssessmentResponse($assessment),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Self-assessment scoring failed', [
                'user_id'  => $user->id,
                'player_id'=> $player->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses self-assessment.',
                'detail'  => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/self-assessment/me",
        operationId: "getMySelfAssessment",
        summary: "Mendapatkan hasil self-assessment terakhir milik user yang sedang login",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"]
    )]
    #[OA\Response(
        response: 200,
        description: "Data assessment terakhir, atau data: null jika belum pernah mengisi",
        content: new OA\JsonContent(ref: "#/components/schemas/SelfAssessmentResult")
    )]
    #[OA\Response(response: 404, description: "Profil player tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function myLatest(Request $request): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Profil player tidak ditemukan.',
            ], 404);
        }

        $assessment = SelfAssessment::where('player_id', $player->id)
            ->latest()
            ->first();

        if (!$assessment) {
            return response()->json([
                'status' => 'success',
                'data'   => null,
                'message'=> 'Anda belum mengisi self-assessment.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatAssessmentResponse($assessment),
        ]);
    }

    #[OA\Get(
        path: "/api/self-assessment",
        operationId: "listSelfAssessments",
        summary: "List semua self-assessment dengan filter (khusus panitia/admin)",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"]
    )]
    #[OA\Parameter(name: "risk_label", in: "query", schema: new OA\Schema(type: "string", enum: ["low", "medium", "high"]))]
    #[OA\Parameter(name: "sport_branch", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "contingent", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "requires_clearance", in: "query", schema: new OA\Schema(type: "boolean"))]
    #[OA\Parameter(name: "is_kacamata", in: "query", schema: new OA\Schema(type: "boolean"))]
    #[OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 20))]
    #[OA\Response(
        response: 200,
        description: "Paginated list assessment",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/SelfAssessmentResult")
                        ),
                        new OA\Property(property: "current_page",   type: "integer", example: 1),
                        new OA\Property(property: "last_page",      type: "integer", example: 5),
                        new OA\Property(property: "per_page",       type: "integer", example: 20),
                        new OA\Property(property: "total",          type: "integer", example: 87),
                        new OA\Property(property: "from",           type: "integer", nullable: true, example: 1),
                        new OA\Property(property: "to",             type: "integer", nullable: true, example: 20),
                        new OA\Property(property: "next_page_url",  type: "string",  nullable: true, example: "/api/self-assessment?page=2"),
                        new OA\Property(property: "prev_page_url",  type: "string",  nullable: true, example: null),
                    ]
                ),
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {
        $query = SelfAssessment::query()
            ->with(['player.user'])
            ->latest();

        if ($risk = $request->query('risk_label')) {
            $query->where('risk_label', $risk);
        }
        if ($sport = $request->query('sport_branch')) {
            $query->whereHas('player.sport', fn($q) => $q->where('name', 'like', "%{$sport}%"));
        }
        if ($cont = $request->query('contingent')) {
            $query->whereHas('player.contingent', fn($q) => $q->where('name', 'like', "%{$cont}%"));
        }
        if ($request->boolean('requires_clearance')) {
            $query->where('requires_clearance', true);
        }
        if ($request->boolean('is_kacamata')) {
            $query->where('is_kacamata_snapshot', true);
        }

        $perPage = min(100, max(5, (int) $request->query('per_page', 20)));
        $paginated = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $paginated,
        ]);
    }

    #[OA\Get(
        path: "/api/self-assessment/player/{playerId}",
        operationId: "getPlayerSelfAssessment",
        summary: "Mendapatkan self-assessment terakhir milik player tertentu",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"],
        description: "Mengembalikan hasil self-assessment terbaru dari player berdasarkan player ID. Response menyertakan `score_breakdown` dan `form_responses` (jawaban mentah). Dapat diakses oleh panitia dan pic_kontingen."
    )]
    #[OA\Parameter(name: "playerId", in: "path", required: true, description: "ID player", schema: new OA\Schema(type: "integer", example: 7))]
    #[OA\Response(
        response: 200,
        description: "Berhasil. `data` berisi hasil assessment jika sudah pernah mengisi, atau `null` disertai `message` jika belum.",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    description: "Player sudah mengisi self-assessment",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "data",   ref: "#/components/schemas/SelfAssessmentResult"),
                    ]
                ),
                new OA\Schema(
                    description: "Player belum mengisi self-assessment",
                    properties: [
                        new OA\Property(property: "status",  type: "string", example: "success"),
                        new OA\Property(property: "data",    type: "string", nullable: true, example: null),
                        new OA\Property(property: "message", type: "string", example: "Player ini belum mengisi self-assessment."),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Player tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function showByPlayer(int $playerId): JsonResponse
    {
        $player = Player::find($playerId);

        if (!$player) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Player tidak ditemukan.',
            ], 404);
        }

        $assessment = SelfAssessment::where('player_id', $playerId)
            ->latest()
            ->first();

        if (!$assessment) {
            return response()->json([
                'status'  => 'success',
                'data'    => null,
                'message' => 'Player ini belum mengisi self-assessment.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatAssessmentResponse($assessment, true),
        ]);
    }

    #[OA\Get(
        path: "/api/self-assessment/{id}",
        operationId: "showSelfAssessment",
        summary: "Detail satu self-assessment berdasarkan ID",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"],
        description: "Jika diakses oleh admin/panitia, response menyertakan score_breakdown (detail skor per pertanyaan) dan form_responses (jawaban mentah). Jika diakses peserta sendiri, dua field tersebut disembunyikan."
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Detail assessment",
        content: new OA\JsonContent(ref: "#/components/schemas/SelfAssessmentResult")
    )]
    #[OA\Response(response: 403, description: "Peserta mencoba mengakses data milik orang lain", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Assessment tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show(Request $request, int $id): JsonResponse
    {
        $assessment = SelfAssessment::with(['player.user'])->find($id);

        if (!$assessment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Self-assessment tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        if (!$user->role === 'panitia') {
            if (!$assessment->player || $assessment->player->user_id !== $user->id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki akses ke data ini.',
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatAssessmentResponse($assessment, true),
        ]);
    }

    #[OA\Post(
        path: "/api/self-assessment/review/{id}",
        operationId: "reviewSelfAssessment",
        summary: "Submit review medis — izinkan atau larang peserta bertanding",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["is_allowed_to_play"],
            properties: [
                new OA\Property(property: "is_allowed_to_play", type: "boolean", description: "true = diizinkan, false = tidak diizinkan"),
                new OA\Property(property: "medical_notes", type: "string", description: "catatan dokter/fisioterapis (max 2000 karakter)"),
                new OA\Property(property: "pic_confirmed", type: "boolean", description: "konfirmasi PIC kontingen sudah diinformasikan")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Review tersimpan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "is_allowed_to_play", type: "boolean"),
                new OA\Property(property: "status_label", type: "string", example: "Diizinkan Bermain")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Assessment tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function submitReview(ReviewSelfAssessmentRequest $request, int $id): JsonResponse
    {
        $assessment = SelfAssessment::find($id);
        if (!$assessment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Self-assessment tidak ditemukan.',
            ], 404);
        }

        $assessment->update([
            'medical_notes'      => $request->input('medical_notes'),
            'is_allowed_to_play' => $request->boolean('is_allowed_to_play'),
            'pic_confirmed'      => $request->boolean('pic_confirmed', $assessment->pic_confirmed),
            'reviewed_at'        => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Review medis berhasil disimpan.',
            'data'    => [
                'id'              => $assessment->id,
                'is_allowed_to_play' => $assessment->is_allowed_to_play,
                'status_label'    => $assessment->is_allowed_to_play
                    ? 'Diizinkan Bermain'
                    : 'Tidak Diizinkan / Istirahat',
                'reviewed_at'     => $assessment->reviewed_at,
            ],
        ]);
    }

    #[OA\Get(
        path: "/api/self-assessment/summary/contingent",
        operationId: "summaryByContingent",
        summary: "Ringkasan jumlah risiko per kontingen/fakultas",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"],
        description: "Hanya menghitung assessment terbaru per player (tidak double-count). Berguna untuk dashboard panitia."
    )]
    #[OA\Response(
        response: 200,
        description: "Array of object",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "contingent", type: "string"),
                    new OA\Property(property: "high_risk_count", type: "integer"),
                    new OA\Property(property: "medium_risk_count", type: "integer"),
                    new OA\Property(property: "low_risk_count", type: "integer"),
                    new OA\Property(property: "kacamata_count", type: "integer"),
                    new OA\Property(property: "total_assessed", type: "integer")
                ]
            )
        )
    )]
    public function summaryByContingent(): JsonResponse
    {
        $latestIds = DB::table('self_assessments')
            ->selectRaw('MAX(id) as id')
            ->groupBy('player_id')
            ->pluck('id');

        $summary = DB::table('self_assessments')
            ->join('players', 'players.id', '=', 'self_assessments.player_id')
            ->leftJoin('contingents', 'contingents.id', '=', 'players.contingent_id')
            ->whereIn('self_assessments.id', $latestIds)
            ->select(
                'contingents.name as contingent',
                DB::raw("COUNT(CASE WHEN risk_label = 'high' THEN 1 END) as high_risk_count"),
                DB::raw("COUNT(CASE WHEN risk_label = 'medium' THEN 1 END) as medium_risk_count"),
                DB::raw("COUNT(CASE WHEN risk_label = 'low' THEN 1 END) as low_risk_count"),
                DB::raw("COUNT(CASE WHEN is_kacamata_snapshot = true THEN 1 END) as kacamata_count"),
                DB::raw('COUNT(*) as total_assessed')
            )
            ->groupBy('contingents.id', 'contingents.name')
            ->orderBy('contingents.name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $summary,
        ]);
    }

    #[OA\Get(
        path: "/api/self-assessment/summary/sport",
        operationId: "summaryBySport",
        summary: "Ringkasan jumlah risiko per cabang olahraga",
        security: [["bearerAuth" => []]],
        tags: ["Self Assessment"],
        description: "Struktur response sama dengan summary/contingent, tapi dikelompokkan berdasarkan sport_branch."
    )]
    #[OA\Response(
        response: 200,
        description: "Array of object",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "sport_branch", type: "string"),
                    new OA\Property(property: "high_risk_count", type: "integer"),
                    new OA\Property(property: "medium_risk_count", type: "integer"),
                    new OA\Property(property: "low_risk_count", type: "integer"),
                    new OA\Property(property: "kacamata_count", type: "integer"),
                    new OA\Property(property: "total_assessed", type: "integer")
                ]
            )
        )
    )]
    public function summaryBySport(): JsonResponse
    {
        $latestIds = DB::table('self_assessments')
            ->selectRaw('MAX(id) as id')
            ->groupBy('player_id')
            ->pluck('id');

        $summary = DB::table('self_assessments')
            ->join('players', 'players.id', '=', 'self_assessments.player_id')
            ->leftJoin('sports', 'sports.id', '=', 'players.sport_id')
            ->whereIn('self_assessments.id', $latestIds)
            ->select(
                'sports.name as sport_branch',
                DB::raw("COUNT(CASE WHEN risk_label = 'high' THEN 1 END) as high_risk_count"),
                DB::raw("COUNT(CASE WHEN risk_label = 'medium' THEN 1 END) as medium_risk_count"),
                DB::raw("COUNT(CASE WHEN risk_label = 'low' THEN 1 END) as low_risk_count"),
                DB::raw("COUNT(CASE WHEN is_kacamata_snapshot = true THEN 1 END) as kacamata_count"),
                DB::raw('COUNT(*) as total_assessed')
            )
            ->groupBy('sports.id', 'sports.name')
            ->orderBy('sports.name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $summary,
        ]);
    }

    private function formatAssessmentResponse(SelfAssessment $a, bool $includeBreakdown = false): array
    {
        $base = [
            'id'                    => $a->id,
            'player_id'             => $a->player_id,
            'player_name'           => $a->player?->name,
            'sport_branch'          => $a->sport_branch_snapshot ?? $a->player?->sport?->name,
            'contingent'            => $a->player?->contingent?->name,
            'snapshot' => [
                'age'         => $a->age_snapshot,
                'bmi'         => $a->bmi_snapshot,
                'is_kacamata' => $a->is_kacamata_snapshot,
            ],
            'risk_label'            => $a->risk_label,
            'total_score'           => $a->total_score,
            'confidence_score'      => $a->confidence_score,
            'requires_clearance'    => $a->requires_clearance,
            'domain_scores' => [
                'cardiovascular'  => $a->score_cardiovascular,
                'musculoskeletal' => $a->score_musculoskeletal,
                'acute_readiness' => $a->score_acute_readiness,
                'psychosocial'    => $a->score_psychosocial,
            ],
            'red_flags'     => $a->red_flags ?? [],
            'yellow_flags'  => $a->yellow_flags ?? [],
            'recommendation'   => $a->recommendation,
            'panitia_summary'  => $a->panitia_summary,
            'questionnaire_version' => $a->questionnaire_version,
            'algorithm_version'     => $a->algorithm_version,
            'valid_until' => $a->valid_until,
            'is_valid'    => $a->isValid(),
            'medical_review' => [
                'medical_notes'      => $a->medical_notes,
                'is_allowed_to_play' => $a->is_allowed_to_play,
                'pic_confirmed'      => $a->pic_confirmed,
                'reviewed_at'        => $a->reviewed_at,
            ],
            'created_at' => $a->created_at,
            'updated_at' => $a->updated_at,
        ];

        if ($includeBreakdown) {
            $base['score_breakdown'] = $a->score_breakdown;
            $base['form_responses']  = $a->form_responses;
        }

        return $base;
    }
}