# 💰 Class & School Cash Management System (Sistem Uang Kas Sekolah)

> **AI Agent Instructions & Development Roadmap**

---

## 📌 Deskripsi Proyek

Proyek ini adalah pengembangan **Sistem Informasi Manajemen Uang Kas Sekolah**, dirancang untuk mencatat, mengelola, dan melaporkan keuangan kas kelas/sekolah secara efisien, transparan, dan akuntabel.

**Tujuan Utama AI Agent:**  
Membangun aplikasi web berbasis Laravel dengan **Role-Based Access Control (RBAC)** untuk 3 peran utama: **Admin/Bendahara Sekolah**, **Bendahara Kelas**, dan **Siswa/Wali Murid**.

---

## 🛠️ Tech Stack & Tooling

| Component         | Technology                      | Description                     |
| :---------------- | :------------------------------ | :------------------------------ |
| **Backend**       | PHP 8.x + Laravel (Latest)      | MVC Architecture & Eloquent ORM |
| **Frontend**      | Laravel Blade + Tailwind CSS    | UI Clean, Modern & Responsive   |
| **Database**      | MySQL                           | Relational Database Management  |
| **Interactivity** | Livewire / Alpine.js (Optional) | Smooth AJAX/Interactive UX      |

---

## 👥 Role-Based Access Control (RBAC)

```
┌─────────────────────────────────────────────────────────┐
│                   PERAN & HAK AKSES                     │
├───────────────────┬─────────────────────────────────────┤
│ 👑 admin          │ Full Access Master Data & Laporan   │
│ 🧑‍💼 bendahara_kelas│ Kelola Tagihan & Catat Kas Kelas    │
│ 👨‍🎓 siswa          │ Cek Tagihan, Riwayat & Upload Bukti │
└───────────────────┴─────────────────────────────────────┘
```

---

## 🗄️ Skema Database (Database Schema)

Tolong buatkan file **Migration** dan **Model** sesuai spesifikasi berikut. Pastikan relasi Eloquent (`hasMany`, `belongsTo`, `hasOne`) terpasang dengan benar.

### 1. Master & Users

- **`users`**: `id`, `name`, `email`, `password`, `role` (`admin`, `bendahara_kelas`, `siswa`), `class_id` (FK to `classes`, nullable), `nisn` (nullable), `created_at`, `updated_at`.
- **`classes`**: `id`, `class_name` (e.g., "X IPA 1"), `academic_year`, `homeroom_teacher` (wali kelas), `created_at`, `updated_at`.

### 2. Transaksi Kas & Tagihan

- **`cash_categories`**: `id`, `name` (e.g., "Uang Kas Mingguan", "Uang Acara"), `type` (`in`, `out`), `description`, `created_at`, `updated_at`.
- **`cash_bills`**: `id`, `class_id` (FK), `category_id` (FK), `title`, `amount` (decimal), `due_date`, `created_at`, `updated_at`.
- **`payments`**: `id`, `cash_bill_id` (FK), `student_id` (FK to `users`), `amount_paid` (decimal), `payment_date`, `status` (`pending`, `verified`, `rejected`), `proof_file` (nullable), `notes`, `verified_by` (FK to `users`, nullable), `created_at`, `updated_at`.
- **`cash_expenses`**: `id`, `class_id` (FK), `category_id` (FK), `title`, `amount` (decimal), `expense_date`, `proof_file` (nullable), `notes`, `created_by` (FK to `users`), `created_at`, `updated_at`.

---

## 🎯 Fitur Utama berdasarkan Peran

### 📊 1. Dashboard & Module Admin (Bendahara Utama / Operator)

- **Dashboard Metrik:** Total pemasukan, pengeluaran, sisa saldo kas sekolah/seluruh kelas, dan grafik tren kas bulanan.
- **Master Data (CRUD):** Kelola data Akun (`users`), Kelola Kelas (`classes`), Kelola Kategori Kas (`cash_categories`).
- **Laporan Global:** Rekapitulasi kas seluruh kelas (Bisa diekspor ke PDF/Excel).

### ✍️ 2. Module Bendahara Kelas

- **Dashboard Kelas:** Ringkasan Saldo Kas Kelas, jumlah siswa yang lunas/menunggak.
- **Kelola Tagihan (Cash Bills):** Membuat tagihan mingguan/bulanan atau kegiatan kelas.
- **Pencatatan Pemasukan (Payments):**
    - Input manual pembayaran siswa secara tunai.
    - Verifikasi bukti pembayaran yang diunggah siswa.
- **Pencatatan Pengeluaran (Cash Expenses):** Catat pengeluaran kas kelas lengkap dengan upload nota/bukti transaksi.
- **Laporan Kas Kelas:** Cetak rekap kas bulanan kelas.

### 📱 3. Module Siswa / Orang Tua

- **Dashboard Siswa:** Ringkasan total tagihan aktif & riwayat pembayaran pribadi.
- **Tagihan Saya:** Melihat riwayat tunggakan kas mingguan.
- **Upload Bukti Bayar:** Upload foto/file resi transfer jika pembayaran dilakukan via bank/e-wallet.
- **Transparansi Kas Kelas:** Melihat laporan pemasukan & pengeluaran kas kelas secara terbuka.

---

## 🤖 AI Implementation Steps (Langkah Pengerjaan)

> **Instruksi untuk AI Agent:** Kerjakan proyek ini secara bertahap (_step-by-step_). Selesaikan setiap kriteria sebelum beralih ke tahap berikutnya.

- [x] **Tahap 1: Setup Framework & Database**
    - Setup proyek Laravel baru.
    - Buat semua file _Migration_ sesuai skema database di atas. Tambahkan foreign key constraint.
    - Buat semua _Model_ beserta relasi Eloquent (`belongsTo`, `hasMany`).
    - Buat _Seeder_ untuk data awal (Admin, Bendahara Kelas, Siswa, Kelas, dan Kategori Kas awal).

- [x] **Tahap 2: Autentikasi & Middleware Access Control**
    - Buat sistem Login & Logout.
    - Buat `RoleMiddleware` untuk memproteksi endpoint (`/admin/*`, `/bendahara/*`, `/siswa/*`).
    - Buat Layout Blade responsif (Sidebar & Navbar) yang otomatis menyesuaikan menu berdasarkan peran user.

- [x] **Tahap 3: Modul Master & Admin (CRUD Data)**
    - Implementasikan Controller & Blade View CRUD `users` (siswa), `classes`, dan `cash_categories`.
    - Tambahkan modul manajemen Tahun Ajaran (`academic_years`) beserta aktivasi status.
    - Buat halaman _Dashboard Admin_ dengan komponen card stat ringkasan keuangan.

- [x] **Tahap 4: Modul Transaksi & Kas Kelas (Bendahara)**
    - Buat fitur pembuatan tagihan kas (`cash_bills`).
    - Buat antarmuka entri pembayaran (`payments`) & verifikasi bukti bayar siswa.
    - Buat fitur pencatatan pengeluaran (`cash_expenses`) dengan fitur unggah nota.

- [x] **Tahap 5: Modul Siswa & Transparansi Keuangan**
    - Buat halaman tagihan pribadi siswa & form upload bukti transaksi.
    - Buat halaman transparansi kas kelas (melihat pemasukan & pengeluaran kelas).

- [x] **Tahap 6: Reporting & Exporting**
    - Tambahkan fitur Cetak/Export Laporan Kas (PDF atau Excel).
    - Poles UI menggunakan Tailwind CSS agar lebih rapi dan intuitif.

- [x] **Tahap 7: Polishing, Security & Optimization**
    - Audit keamanan rute & kebijakan otorisasi menggunakan Laravel Policy/Gate.
    - Terapkan Eager Loading (`with()`) pada setiap query Eloquent (N+1 fix).
    - Buat UI Error Pages kustom (403, 404, 500) sesuai tema Tailwind CSS.
    - Lengkapi validasi frontend & toast notification untuk kenyamanan UX.
    - Uji seluruh alur dari awal sampai akhir.

---

## 💡 Catatan Khusus untuk AI Agent

1. **Desain UI:** Gunakan Tailwind CSS dengan warna bertema keuangan yang modern (misal: aksen Emerald/Green untuk pemasukan, Rose/Red untuk pengeluaran, dan Indigo/Blue untuk navigasi).
2. **Validasi Form:** Selalu terapkan validasi request di setiap form controller (`required`, `numeric`, `mimes:jpg,png,pdf` untuk file).
3. **Keamanan & Scoping:** Pastikan query data bendahara dan siswa hanya menampilkan data yang sesuai dengan `class_id` mereka.

```

```

### 💡 Cara Menggunakan File Ini:

1. Simpan kode di atas ke dalam file bernama `README.md` di folder root project Laravel Anda.
2. Ketika mulai menggunakan AI Agent (misal: Cursor / Copilot), Anda cukup instruksikan:
    > _"Tolong baca file `README.md` dan mulai kerjakan **Tahap 1** terlebih dahulu."_
