# 💰 Sistem Manajemen Uang Kas Sekolah

Aplikasi web untuk mencatat, mengelola, dan melaporkan keuangan kas kelas & sekolah secara transparan dan akuntabel. Dibangun dengan **Laravel**, **Tailwind CSS**, dan **Alpine.js**.

---

## 📸 Fitur Utama

| Fitur | Keterangan |
|-------|------------|
| 📊 Dashboard Analytics | KPI saldo, pemasukan/pengeluaran, grafik tren 6 bulan, perbandingan per kelas |
| 👥 Manajemen Master Data | CRUD Siswa, Kelas, Tahun Ajaran, Kategori Kas |
| 💵 Tagihan & Pembayaran | Buat tagihan per kelas, catat pembayaran tunai/transfer, verifikasi bukti bayar |
| 💸 Pencatatan Pengeluaran | Catat pengeluaran kas + upload nota/bukti |
| 📄 Laporan & Export | Buku mutasi kas dengan saldo berjalan, rekap tunggakan — export ke **Excel & PDF** |
| 📱 Portal Siswa | Cek tagihan pribadi, upload bukti transfer, lihat transparansi kas kelas |
| 🔐 Keamanan | Role-Based Access (Admin, Bendahara Kelas, Siswa), Policy/Gate, custom error pages |

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.x + Laravel
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Database:** MySQL
- **Export:** Maatwebsite Excel + Barryvdh DomPDF
- **Charts:** Chart.js (CDN)

---

## ⚙️ Persyaratan Sistem

Pastikan komputer kamu sudah terinstal:

- **PHP** ≥ 8.2 (dengan ekstensi: `zip`, `gd`, `mbstring`, `pdo_mysql`)
- **Composer** (package manager PHP)
- **MySQL** (bisa pakai Laragon / XAMPP / MAMP)
- **Node.js** ≥ 18 + **npm** (untuk compile asset Tailwind CSS)
- **Git**

> 💡 **Rekomendasi:** Gunakan [Laragon](https://laragon.org/) di Windows — sudah include PHP, MySQL, dan phpMyAdmin.

---

## 🚀 Cara Instalasi (Step-by-Step)

### 1. Clone Repository

```bash
git clone https://github.com/ilhampram/KasSekolah.git
cd KasSekolah
```

### 2. Install Dependency PHP

```bash
composer install
```

### 3. Install Dependency Frontend

```bash
npm install
```

### 4. Salin File Environment

```bash
cp .env.example .env
```

> Di Windows (CMD), gunakan: `copy .env.example .env`

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

Buka file `.env` dan sesuaikan bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_uang_kas_sekolah
DB_USERNAME=root
DB_PASSWORD=
```

> ⚠️ Pastikan kamu sudah **membuat database** `db_uang_kas_sekolah` di MySQL/phpMyAdmin terlebih dahulu.

### 7. Jalankan Migrasi & Seeder

```bash
php artisan migrate --seed
```

Perintah ini akan membuat semua tabel dan mengisi data awal (akun demo, kelas, kategori kas).

### 8. Buat Symbolic Link untuk Storage

```bash
php artisan storage:link
```

### 9. Build Asset Frontend (Tailwind CSS)

```bash
npm run build
```

### 10. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser dan akses: **http://localhost:8000**

---

## 🔑 Akun Demo

Setelah menjalankan seeder, kamu bisa login dengan akun berikut:

| Role | Email | Password |
|------|-------|----------|
| 👑 Admin / Bendahara Sekolah | `admin@sekolah.test` | `password` |
| 👨‍🎓 Siswa 1 | `andi@siswa.test` | `password` |
| 👨‍🎓 Siswa 2 | `citra@siswa.test` | `password` |

---

## 📖 Cara Menggunakan Aplikasi

### 🔹 Sebagai Admin / Bendahara Sekolah

1. **Login** dengan akun admin.
2. **Dashboard** — Lihat ringkasan saldo, pemasukan, pengeluaran, grafik tren bulanan.
3. **Master Data** — Kelola Tahun Ajaran, Kelas, Siswa, dan Kategori Kas dari menu sidebar.
4. **Tagihan Siswa** — Buat tagihan kas per kelas (misal: Kas Bulanan Rp 50.000).
5. **Kasir & Pembayaran** — Catat pembayaran tunai siswa atau verifikasi bukti transfer yang diupload siswa.
6. **Transaksi Kas** — Catat pemasukan/pengeluaran kas kelas.
7. **Laporan Keuangan** — Lihat buku mutasi kas dengan saldo berjalan, rekap tunggakan per siswa, lalu **export ke Excel atau PDF**.

### 🔹 Sebagai Siswa

1. **Login** dengan akun siswa.
2. **Dashboard** — Lihat ringkasan tagihan pribadi dan saldo kas kelas.
3. **Histori Pembayaran** — Lihat semua riwayat pembayaran kamu.
4. **Upload Bukti Transfer** — Jika bayar via transfer bank/e-wallet, upload foto resi agar diverifikasi bendahara.
5. **Transparansi Kas** — Lihat laporan pemasukan & pengeluaran kas kelas secara terbuka.

---

## 📁 Struktur Folder Penting

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Controller modul admin
│   │   └── Siswa/          # Controller modul siswa
│   ├── Models/             # Eloquent models
│   ├── Policies/           # Authorization policies
│   └── Exports/            # Export Excel classes
├── database/
│   ├── migrations/         # Skema tabel database
│   └── seeders/            # Data awal (demo)
├── resources/views/
│   ├── layouts/            # Layout utama (sidebar, navbar)
│   ├── admin/              # Views modul admin
│   ├── siswa/              # Views modul siswa
│   └── errors/             # Custom error pages (403, 404, 500)
├── routes/web.php          # Semua route definitions
└── .env                    # Konfigurasi environment
```

---

## 👥 Role & Hak Akses

| Role | Hak Akses |
|------|-----------|
| **Admin** | Full access: master data, tagihan, pembayaran, pengeluaran, laporan, export |
| **Bendahara Kelas** | Kelola tagihan & kas di kelas yang ditugaskan |
| **Siswa** | Lihat tagihan pribadi, upload bukti bayar, lihat transparansi kas |

---

## 🐛 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `SQLSTATE[HY000] [1049] Unknown database` | Buat database `db_uang_kas_sekolah` di phpMyAdmin terlebih dahulu |
| `Vite manifest not found` | Jalankan `npm run build` terlebih dahulu |
| `The stream or file ... could not be opened` | Jalankan `php artisan storage:link` |
| `Class not found` | Jalankan `composer dump-autoload` |
| Export Excel error | Pastikan ekstensi `ext-zip` aktif di `php.ini` |

---

## 📝 Lisensi

Proyek ini dibuat untuk keperluan edukasi dan tugas sekolah.

---

> Dibuat dengan ❤️ menggunakan Laravel + Tailwind CSS
