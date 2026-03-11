# ⚽ Sistem Reservasi Futsal (Futsal-Go)

Aplikasi manajemen booking lapangan futsal modern yang dibangun dengan **TALL Stack** (Tailwind, Alpine.js, Laravel, Livewire) dan **Filament PHP**. Sistem ini dirancang untuk mempermudah pengelola lapangan dalam memanajemen jadwal dan memudahkan penyewa dalam melakukan pembayaran secara otomatis.

---

## 🚀 Fitur Utama

- **Dashboard Admin (Filament):** Kelola jadwal, lapangan, dan laporan pendapatan dengan mudah.
- **Real-time Booking (Livewire):** Pilih jam dan lapangan secara interaktif tanpa reload halaman.
- **Payment Gateway (Midtrans):** Pembayaran otomatis menggunakan QRIS, Bank Transfer, atau E-Wallet.
- **WhatsApp Notification (Fonnte):** Pengiriman bukti booking dan pengingat jadwal otomatis ke penyewa.
- **Responsive Design (Tailwind CSS):** Tampilan optimal di HP maupun Desktop.

---

## 🛠️ Stack Teknologi

- **Framework:** Laravel 12
- **Admin Panel:** [Filament PHP](https://filamentphp.com/)
- **Frontend:** [Livewire](https://livewire.laravel.com/), [Alpine.js](https://alpinejs.dev/), [Tailwind CSS](https://tailwindcss.com/)
- **Payment:** [Midtrans](https://midtrans.com/)
- **Messaging:** [Fonnte](https://fonnte.com/)

---

## 💻 Cara Instalasi

Pastikan kamu sudah menginstall **PHP >= 8.2**, **Composer**, dan **Node.js** di komputermu.

1. **Clone repositori:**
   ```bash
   git clone (https://github.com/Ainulqodri/sistem-reservasi-futsal.git)
   cd nama_folder_projek
2. **Install dependencies:**
   ```bash
   composer install
   npm install && npm run build
3. **Setup environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
4. **Konfigurasi API:**
   ```bash
   MIDTRANS_SERVER_KEY=your_server_key
   MIDTRANS_CLIENT_KEY=your_client_key
   FONNTE_TOKEN=your_fonnte_token
5. **Migrate & Seed:**
   ```bash
   php artisan migrate --seed
6. **Jalankan Server:**
   ```bash
   php artisan serve
