# TelUCup Migration Plan: Next.js → Laravel Full Blade

## Status: In Progress

## Tahap Migrasi

| Tahap | Deskripsi | Status |
|-------|-----------|--------|
| 1 | Audit project & mapping route | ✅ Done |
| 2 | Setup layout, asset, komponen dasar | ✅ Done |
| 3 | Session auth & role middleware | ✅ Done |
| 4 | Dashboard skeleton per role | ✅ Done |
| 5 | Migrasi fitur read-only | 🔲 Pending |
| 6 | Migrasi form sederhana | 🔲 Pending |
| 7 | Migrasi fitur kompleks | 🔲 Pending |
| 8 | Cleanup & optimasi | 🔲 Pending |

## File yang Dibuat/Diubah (Tahap 2-4)

### Layouts
- `resources/views/layouts/dashboard.blade.php` — Layout utama dashboard (sidebar + navbar + slot)
- `resources/views/layouts/guest.blade.php` — Layout guest (redesign branding)

### Components
- `resources/views/components/alert.blade.php` — Flash message
- `resources/views/components/badge.blade.php` — Status badge
- `resources/views/components/stats-card.blade.php` — Dashboard stats card
- `resources/views/components/empty-state.blade.php` — Empty state UI
- `resources/views/components/sidebar-link.blade.php` — Sidebar menu item
- `resources/views/components/primary-button.blade.php` — Updated brand color

### Views
- `resources/views/auth/login.blade.php` — Redesign login (match frontend lama)
- `resources/views/public/home.blade.php` — Landing page publik
- `resources/views/dashboard/panitia/index.blade.php` — Panitia dashboard
- `resources/views/dashboard/player/index.blade.php` — Player dashboard
- `resources/views/dashboard/pic-kontingen/index.blade.php` — PIC dashboard
- `resources/views/partials/sidebar-panitia.blade.php` — Menu panitia
- `resources/views/partials/sidebar-player.blade.php` — Menu player
- `resources/views/partials/sidebar-pic.blade.php` — Menu PIC

### Controllers
- `app/Http/Controllers/Web/HomeController.php` — Halaman publik
- `app/Http/Controllers/Web/Panitia/DashboardController.php` — Dashboard panitia
- `app/Http/Controllers/Web/Player/DashboardController.php` — Dashboard player
- `app/Http/Controllers/Web/PicKontingen/DashboardController.php` — Dashboard PIC

### Middleware & Auth
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` — Role-based redirect
- `app/Http/Middleware/RoleMiddleware.php` — Dual-mode (web + API)

### Config
- `routes/web.php` — Web routes
- `resources/css/app.css` — Brand theme CSS
- `tailwind.config.js` — Inter font + brand colors
- `app/View/Components/Layouts/Dashboard.php` — Dashboard layout component

## API yang Dipertahankan

Semua endpoint di `routes/api.php` tetap berfungsi tanpa perubahan.

## Keputusan Arsitektur

1. **Session auth** via Breeze (sudah ada), bukan token localStorage
2. **RoleMiddleware** dual-mode: JSON untuk API, redirect untuk web
3. **Dashboard layout** menggunakan Blade component (`<x-layouts.dashboard>`)
4. **Sidebar** menggunakan partials (`@include`) per role
5. **Alpine.js** untuk interaksi kecil (toggle sidebar, show/hide password)
6. **Chatbot** dihapus sesuai keputusan user
