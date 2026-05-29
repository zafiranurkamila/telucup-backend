<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "2.0.0",
    title: "TelU Cup Backend API",
    description: "Dokumentasi lengkap API manajemen kompetisi internal Tel-U Cup. Mencakup autentikasi, manajemen cabang olahraga, kontingen, registrasi tim, dan bagan pertandingan gugur."
)]
#[OA\Server(url: "http://localhost:8000", description: "Development Server")]
#[OA\SecurityScheme(securityScheme: "bearerAuth", type: "http", scheme: "bearer", bearerFormat: "JWT")]

// ── Tags ────────────────────────────────────────────────────────────────────
#[OA\Tag(name: "Authentication",  description: "Register dan login pengguna")]
#[OA\Tag(name: "Sports",          description: "Manajemen cabang olahraga dan sub-kategori")]
#[OA\Tag(name: "Contingents",     description: "Manajemen kontingen (fakultas/divisi) dan keanggotaan")]
#[OA\Tag(name: "Registrations",   description: "Pendaftaran tim kontingen ke cabang olahraga")]
#[OA\Tag(name: "Bracket",         description: "Bagan pertandingan sistem gugur")]
#[OA\Tag(name: "Players",         description: "Manajemen data pemain")]
#[OA\Tag(name: "Self Assessment", description: "Deteksi risiko kesehatan peserta")]
#[OA\Tag(name: "Verification",    description: "Check-in pemain di lapangan")]
#[OA\Tag(name: "Event Photo",     description: "Upload foto event untuk AI face recognition")]
#[OA\Tag(name: "Gallery",         description: "Galeri foto pemain hasil deteksi AI")]
#[OA\Tag(name: "Admin",           description: "Endpoint administrasi internal")]

// ── Reusable Schemas ────────────────────────────────────────────────────────
#[OA\Schema(
    schema: "ErrorResponse",
    description: "Format respon error standar",
    properties: [
        new OA\Property(property: "status",  type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Pesan error yang menjelaskan penyebab kegagalan."),
    ]
)]
#[OA\Schema(
    schema: "SuccessMessage",
    description: "Format respon sukses tanpa data",
    properties: [
        new OA\Property(property: "status",  type: "string", example: "success"),
        new OA\Property(property: "message", type: "string", example: "Operasi berhasil."),
    ]
)]
#[OA\Schema(
    schema: "UserObject",
    description: "Data akun pengguna",
    properties: [
        new OA\Property(property: "id",          type: "integer", example: 1),
        new OA\Property(property: "name",        type: "string",  example: "Budi Santoso"),
        new OA\Property(property: "email",       type: "string",  format: "email", example: "budi@telkomuniversity.ac.id"),
        new OA\Property(property: "role",        type: "string",  enum: ["panitia", "pic_kontingen", "player"], example: "player"),
        new OA\Property(property: "is_kacamata", type: "boolean", example: false),
        new OA\Property(property: "created_at",  type: "string",  format: "date-time"),
        new OA\Property(property: "updated_at",  type: "string",  format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "SportCategoryObject",
    description: "Sub-kategori dari sebuah cabang olahraga",
    properties: [
        new OA\Property(property: "id",          type: "integer", example: 2),
        new OA\Property(property: "sport_id",    type: "integer", example: 1),
        new OA\Property(property: "name",        type: "string",  example: "Tunggal Putra"),
        new OA\Property(property: "max_members", type: "integer", nullable: true, example: 1),
        new OA\Property(property: "created_at",  type: "string",  format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "SportObject",
    description: "Cabang olahraga beserta sub-kategorinya",
    properties: [
        new OA\Property(property: "id",          type: "integer", example: 1),
        new OA\Property(property: "name",        type: "string",  example: "Badminton"),
        new OA\Property(property: "icon_path",   type: "string",  nullable: true, example: "icons/badminton.png"),
        new OA\Property(property: "max_members", type: "integer", nullable: true, example: null,
            description: "Diisi jika sport tidak memiliki sub-kategori"),
        new OA\Property(property: "categories",  type: "array",
            items: new OA\Items(ref: "#/components/schemas/SportCategoryObject")),
        new OA\Property(property: "created_at",  type: "string",  format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "ContingentObject",
    description: "Kontingen yang mewakili satu fakultas atau divisi",
    properties: [
        new OA\Property(property: "id",                   type: "integer", example: 3),
        new OA\Property(property: "name",                 type: "string",  example: "Fakultas Informatika"),
        new OA\Property(property: "pic_user_id",          type: "integer", nullable: true, example: 5),
        new OA\Property(property: "cloudinary_public_id", type: "string",  nullable: true,
            example: "telucup/contingents/abc123"),
        new OA\Property(property: "image_url",            type: "string",  nullable: true,
            example: "https://res.cloudinary.com/demo/image/upload/v1/telucup/contingents/abc123.jpg",
            description: "URL gambar kontingen dari Cloudinary. null jika belum ada gambar."),
        new OA\Property(property: "pic",                  nullable: true,
            ref: "#/components/schemas/UserObject"),
        new OA\Property(property: "players_count",        type: "integer", example: 12,
            description: "Hanya tersedia pada endpoint list"),
        new OA\Property(property: "created_at",           type: "string",  format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "PlayerObject",
    description: "Data pemain (athlete)",
    properties: [
        new OA\Property(property: "id",                  type: "integer", example: 7),
        new OA\Property(property: "user_id",             type: "integer", example: 10),
        new OA\Property(property: "name",                type: "string",  example: "Ahmad Fauzi"),
        new OA\Property(property: "nim_nip",             type: "string",  nullable: true, example: "1301234567"),
        new OA\Property(property: "sport_id",            type: "integer", nullable: true, example: 1),
        new OA\Property(property: "sport_category_id",   type: "integer", nullable: true, example: 2),
        new OA\Property(property: "contingent_id",       type: "integer", nullable: true, example: 3),
        new OA\Property(property: "photo_path",          type: "string",  nullable: true, example: "https://res.cloudinary.com/..."),
        new OA\Property(property: "verification_status", type: "string",  nullable: true, example: "verified"),
        new OA\Property(property: "checked_in_at",       type: "string",  format: "date-time", nullable: true),
        new OA\Property(property: "employee_status",     type: "string",  nullable: true, example: "Mahasiswa"),
        new OA\Property(property: "work_location",       type: "string",  nullable: true),
        new OA\Property(property: "created_at",          type: "string",  format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "RegistrationObject",
    description: "Pendaftaran tim kontingen untuk satu cabang olahraga. Alur status: draft → submitted → verified/rejected.",
    properties: [
        new OA\Property(property: "id",              type: "integer", example: 1),
        new OA\Property(property: "status",          type: "string",
            enum: ["draft", "submitted", "verified", "rejected"],
            example: "draft",
            description: "draft: PIC masih menyusun tim; submitted: sudah diajukan ke panitia; verified: disetujui; rejected: ditolak"),
        new OA\Property(property: "contingent",      ref: "#/components/schemas/ContingentObject"),
        new OA\Property(property: "sport",           ref: "#/components/schemas/SportObject"),
        new OA\Property(property: "sport_category",  nullable: true, ref: "#/components/schemas/SportCategoryObject"),
        new OA\Property(property: "max_members",     type: "integer", nullable: true, example: 5),
        new OA\Property(property: "current_members", type: "integer", example: 3),
        new OA\Property(property: "slots_remaining", type: "integer", nullable: true, example: 2),
        new OA\Property(property: "players",         type: "array",
            items: new OA\Items(ref: "#/components/schemas/PlayerObject")),
        new OA\Property(property: "created_at",      type: "string",  format: "date-time"),
        new OA\Property(property: "updated_at",      type: "string",  format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "ComplianceRegisteredEntry",
    description: "Satu kontingen yang sudah mendaftar dalam konteks compliance check",
    properties: [
        new OA\Property(property: "registration_id", type: "integer", example: 7),
        new OA\Property(property: "status",          type: "string",
            enum: ["draft", "submitted", "verified", "rejected"],
            example: "submitted"),
        new OA\Property(property: "contingent",      ref: "#/components/schemas/ContingentObject"),
    ]
)]
#[OA\Schema(
    schema: "ComplianceRow",
    description: "Baris kepatuhan pendaftaran untuk satu kombinasi sport/sub-kategori",
    properties: [
        new OA\Property(property: "sport",
            type: "object",
            description: "Cabang olahraga (hanya id, name, icon_path)",
            properties: [
                new OA\Property(property: "id",        type: "integer", example: 1),
                new OA\Property(property: "name",      type: "string",  example: "Badminton"),
                new OA\Property(property: "icon_path", type: "string",  nullable: true),
            ]
        ),
        new OA\Property(property: "sport_category",
            type: "object",
            nullable: true,
            description: "Sub-kategori (null jika sport tidak memiliki kategori)",
            properties: [
                new OA\Property(property: "id",   type: "integer", example: 2),
                new OA\Property(property: "name", type: "string",  example: "Putra"),
            ]
        ),
        new OA\Property(property: "total_contingents",     type: "integer", example: 10,
            description: "Total seluruh kontingen yang terdaftar di sistem"),
        new OA\Property(property: "registered_count",      type: "integer", example: 7,
            description: "Jumlah kontingen yang sudah membuat pendaftaran (status apapun)"),
        new OA\Property(property: "not_registered_count",  type: "integer", example: 3,
            description: "Jumlah kontingen yang belum mendaftar sama sekali"),
        new OA\Property(property: "compliance_rate",       type: "number", format: "float", example: 70.0,
            description: "Persentase kontingen yang sudah mendaftar"),
        new OA\Property(property: "registered",            type: "array",
            description: "Daftar kontingen yang sudah mendaftar beserta status pendaftarannya",
            items: new OA\Items(ref: "#/components/schemas/ComplianceRegisteredEntry")),
        new OA\Property(property: "not_registered",        type: "array",
            description: "Daftar kontingen yang belum mendaftar sama sekali",
            items: new OA\Items(ref: "#/components/schemas/ContingentObject")),
    ]
)]
#[OA\Schema(
    schema: "TeamInMatch",
    description: "Tim yang bertanding dalam satu pertandingan",
    properties: [
        new OA\Property(property: "registration_id", type: "integer", example: 4),
        new OA\Property(property: "contingent",      ref: "#/components/schemas/ContingentObject"),
        new OA\Property(property: "players",         type: "array",
            items: new OA\Items(ref: "#/components/schemas/PlayerObject")),
    ]
)]
#[OA\Schema(
    schema: "MatchObject",
    description: "Pertandingan dalam bagan gugur",
    properties: [
        new OA\Property(property: "id",             type: "integer", example: 12),
        new OA\Property(property: "round",          type: "integer", example: 2),
        new OA\Property(property: "round_name",     type: "string",  example: "Semifinal"),
        new OA\Property(property: "match_number",   type: "integer", example: 1),
        new OA\Property(property: "status",         type: "string",  enum: ["scheduled", "live", "finished", "bye"]),
        new OA\Property(property: "match_date",     type: "string",  format: "date",  nullable: true, example: "2026-06-15"),
        new OA\Property(property: "match_time",     type: "string",  nullable: true,  example: "09:00"),
        new OA\Property(property: "location",       type: "string",  nullable: true,  example: "Gedung Sport Center Lt. 2"),
        new OA\Property(property: "referee_name",   type: "string",  nullable: true,  example: "Budi Santoso"),
        new OA\Property(property: "notes",          type: "string",  nullable: true),
        new OA\Property(property: "score_a",        type: "integer", example: 3),
        new OA\Property(property: "score_b",        type: "integer", example: 1),
        new OA\Property(property: "team_a",         nullable: true, ref: "#/components/schemas/TeamInMatch"),
        new OA\Property(property: "team_b",         nullable: true, ref: "#/components/schemas/TeamInMatch"),
        new OA\Property(property: "winner",         type: "object",  nullable: true,
            properties: [
                new OA\Property(property: "registration_id", type: "integer"),
                new OA\Property(property: "contingent", ref: "#/components/schemas/ContingentObject"),
            ]
        ),
        new OA\Property(property: "is_third_place_match", type: "boolean", example: false,
            description: "true jika ini adalah pertandingan perebutan juara 3"),
        new OA\Property(property: "next_match_id",   type: "integer", nullable: true, example: 15),
        new OA\Property(property: "next_match_slot", type: "string",  nullable: true, enum: ["a", "b"]),
    ]
)]
#[OA\Schema(
    schema: "CheckinPlayerItem",
    description: "Data player beserta status checkin untuk sebuah pertandingan",
    properties: [
        new OA\Property(property: "id",            type: "integer", example: 7),
        new OA\Property(property: "name",          type: "string",  example: "Ahmad Fauzi"),
        new OA\Property(property: "nim_nip",       type: "string",  nullable: true, example: "1301234567"),
        new OA\Property(property: "photo_path",    type: "string",  nullable: true, example: "https://res.cloudinary.com/example/photo.jpg"),
        new OA\Property(property: "checked_in",    type: "boolean", example: false,
            description: "true jika player sudah melakukan checkin untuk pertandingan ini"),
        new OA\Property(property: "checked_in_at", type: "string",  format: "date-time", nullable: true,
            example: "2026-06-15T08:30:00+07:00"),
    ]
)]
#[OA\Schema(
    schema: "CheckinTeam",
    description: "Tim dalam konteks checkin pertandingan, berisi daftar player dengan status kehadiran",
    properties: [
        new OA\Property(property: "registration_id", type: "integer", example: 4),
        new OA\Property(property: "slot",            type: "string",  enum: ["a", "b"], example: "a",
            description: "Posisi tim dalam pertandingan: 'a' = tim tuan rumah, 'b' = tim tamu"),
        new OA\Property(property: "contingent",      ref: "#/components/schemas/ContingentObject"),
        new OA\Property(property: "players",         type: "array",
            items: new OA\Items(ref: "#/components/schemas/CheckinPlayerItem")),
    ]
)]
#[OA\Schema(
    schema: "MatchCheckinData",
    description: "Data checkin sebuah pertandingan, menampilkan semua player kedua tim beserta status kehadiran",
    properties: [
        new OA\Property(property: "match_id",     type: "integer", example: 12),
        new OA\Property(property: "round_name",   type: "string",  example: "Semifinal"),
        new OA\Property(property: "match_number", type: "integer", example: 1),
        new OA\Property(property: "status",       type: "string",  enum: ["scheduled", "live", "finished", "bye"], example: "scheduled"),
        new OA\Property(property: "team_a",       nullable: true, ref: "#/components/schemas/CheckinTeam"),
        new OA\Property(property: "team_b",       nullable: true, ref: "#/components/schemas/CheckinTeam"),
    ]
)]
#[OA\Schema(
    schema: "CheckinRecord",
    description: "Record checkin satu player pada satu pertandingan",
    properties: [
        new OA\Property(property: "game_id",         type: "integer", example: 12),
        new OA\Property(property: "player_id",       type: "integer", example: 7),
        new OA\Property(property: "registration_id", type: "integer", example: 4,
            description: "ID registrasi tim asal player (tim A atau tim B)"),
        new OA\Property(property: "checked_in",      type: "boolean", example: true),
        new OA\Property(property: "checked_in_at",   type: "string",  format: "date-time", nullable: true,
            example: "2026-06-15T08:30:00+07:00"),
    ]
)]
#[OA\Schema(
    schema: "TodayMatchItem",
    description: "Pertandingan hari ini milik kontingen PIC. Identik dengan MatchObject ditambah field `my_slot`.",
    allOf: [new OA\Schema(ref: "#/components/schemas/MatchObject")],
    properties: [
        new OA\Property(property: "my_slot", type: "string", enum: ["a", "b"], example: "a",
            description: "Slot posisi kontingen PIC dalam pertandingan ini: 'a' atau 'b'"),
    ]
)]
#[OA\Schema(
    schema: "BracketRoundItem",
    description: "Satu ronde dalam bagan pertandingan",
    properties: [
        new OA\Property(property: "round",   type: "integer", example: 1),
        new OA\Property(property: "name",    type: "string",  example: "Perempat Final"),
        new OA\Property(property: "matches", type: "array",
            items: new OA\Items(ref: "#/components/schemas/MatchObject")),
    ]
)]
#[OA\Schema(
    schema: "BracketResults",
    description: "Hasil akhir turnamen: juara 1, 2, dan 3",
    properties: [
        new OA\Property(property: "juara1", nullable: true,
            description: "Pemenang Final (Juara 1)",
            properties: [
                new OA\Property(property: "registration_id", type: "integer", example: 3),
                new OA\Property(property: "contingent", ref: "#/components/schemas/ContingentObject"),
            ], type: "object"
        ),
        new OA\Property(property: "juara2", nullable: true,
            description: "Runner-up / kalah di Final (Juara 2)",
            properties: [
                new OA\Property(property: "registration_id", type: "integer", example: 7),
                new OA\Property(property: "contingent", ref: "#/components/schemas/ContingentObject"),
            ], type: "object"
        ),
        new OA\Property(property: "juara3", nullable: true,
            description: "Pemenang pertandingan perebutan Juara 3",
            properties: [
                new OA\Property(property: "registration_id", type: "integer", example: 5),
                new OA\Property(property: "contingent", ref: "#/components/schemas/ContingentObject"),
            ], type: "object"
        ),
    ]
)]
#[OA\Schema(
    schema: "BracketRound",
    description: "Tampilan lengkap bagan pertandingan, termasuk semua ronde, pertandingan perebutan juara 3, dan hasil akhir turnamen",
    properties: [
        new OA\Property(property: "sport",             type: "object", nullable: true),
        new OA\Property(property: "sport_category",    type: "object", nullable: true),
        new OA\Property(property: "total_rounds",      type: "integer", example: 3),
        new OA\Property(property: "rounds",            type: "array",
            description: "Ronde-ronde utama bagan (tidak termasuk pertandingan juara 3)",
            items: new OA\Items(ref: "#/components/schemas/BracketRoundItem")),
        new OA\Property(property: "third_place_match", nullable: true,
            ref: "#/components/schemas/MatchObject",
            description: "Pertandingan perebutan Juara 3 (loser Semifinal). null jika belum ada (kurang dari 4 tim)."),
        new OA\Property(property: "results",           ref: "#/components/schemas/BracketResults",
            description: "Hasil akhir: Juara 1, 2, dan 3. Diisi bertahap seiring pertandingan selesai."),
    ]
)]
#[OA\Schema(
    schema: "PaginationMeta",
    description: "Metadata paginasi Laravel",
    properties: [
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "last_page",    type: "integer", example: 5),
        new OA\Property(property: "per_page",     type: "integer", example: 15),
        new OA\Property(property: "total",        type: "integer", example: 72),
        new OA\Property(property: "from",         type: "integer", example: 1),
        new OA\Property(property: "to",           type: "integer", example: 15),
    ]
)]
abstract class Controller {}
