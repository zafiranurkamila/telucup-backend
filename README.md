# 🏆 Tel-U Cup Backend System

Selamat datang di repository backend **Tel-U Cup**, sistem manajemen turnamen olahraga terbesar di Telkom University. Sistem ini dibangun menggunakan **Laravel** dengan arsitektur modular untuk mendukung berbagai kebutuhan operasional, mulai dari kesehatan pemain hingga manajemen bagan pertandingan.

## 🚀 Fitur Utama

### 1. 🏥 Modul Self-Assessment & AI Risk Filtering (FR-01)
Sistem penyaringan kesehatan otomatis untuk memastikan keselamatan pemain.
- Form kesehatan detail (Skala nyeri, riwayat cedera, kondisi fisik).
- **Smarter AI Logic**: Klasifikasi risiko otomatis (Low/Moderate/High) berdasarkan kata kunci medis dan skala nyeri.
- **Rekomendasi Red Flag**: Memberikan saran medis otomatis bagi pemain berisiko tinggi.
- **Panel Peninjauan Medis**: Dashboard khusus dokter/fisioterapis untuk memberikan izin bertanding.

### 2. 📊 Sistem Bagan Otomatis / Auto-Bracket (FR-02)
Manajemen turnamen yang efisien dan real-time.
- **Auto-Generate Bracket**: Membuat bagan pertandingan secara otomatis dari daftar peserta.
- **Auto-Advance Winner**: Pemenang pertandingan otomatis masuk ke slot babak berikutnya.
- **Detail Match Info**: Mencatat skor, wasit, lokasi (Sport Center), hingga statistik pemain terbaik.

### 3. 🛡️ Dashboard Admin SDM (Super Admin)
Pusat kendali seluruh data master kompetisi.
- **Manajemen Template**: Pengaturan tahun kompetisi aktif (2025/2026).
- **Manajemen Registrasi**: Verifikasi pendaftaran kontingen (Bulutangkis, Futsal, dll) secara masal.
- **Data Pegawai**: Integrasi NIP dan Lokasi Kerja untuk verifikasi identitas resmi.

### 4. 📢 Kampanye Budaya HEI & Safety (FR-03)
Modul edukasi otomatis bagi seluruh peserta.
- Pop-up kampanye nilai **Harmony, Excellence, Integrity**.
- Checkpoint keselamatan (Fair Play, Lapor Cedera).

### 5. 🤖 Smart Assistant & Gallery AI
- **Smart Assistant**: Chatbot helpdesk berbasis menu untuk menjawab pertanyaan seputar jadwal, aturan, dan medis.
- **Smart Gallery**: Infrastruktur database untuk integrasi Face Recognition pada foto pertandingan.

---

## 🛠️ Instalasi & Persiapan

Ikuti langkah berikut untuk menjalankan sistem di komputer lokal Anda:

1. **Clone Repository**
2. **Instal Dependensi**
   ```powershell
   composer install
   ```
3. **Konfigurasi Database**
   Edit file `.env`, sesuaikan dengan database MySQL Anda:
   ```env
   DB_DATABASE=telucup
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. **Migrasi & Seeding Data (Sangat Direkomendasikan)**
   Jalankan perintah ini untuk mendapatkan semua data demo (Pemain, Cabang Olahraga, Bagan):
   ```powershell
   php artisan migrate:fresh --seed
   ```
5. **Jalankan Server**
   ```powershell
   php artisan serve
   ```

---

## 🔑 Akun Demo
Data akun untuk keperluan testing dan demo dapat dilihat langsung di dalam file `database/seeders/DatabaseSeeder.php`. Harap segera ubah password default setelah melakukan instalasi di lingkungan produksi.

---

## 📡 Perintah Artisan Khusus
- `php artisan sync:contingents`: Menarik data kontingen resmi dari server Telkom University.

---
*© 2024 Telkom University - Advanced Agentic Coding Project*
