# 💰 Sistem Manajemen Uang Kas Sekolah

Aplikasi web untuk mencatat, mengelola, dan melaporkan keuangan kas kelas & sekolah secara transparan dan akuntabel. Dibangun dengan **Laravel**, **Tailwind CSS**, dan **Alpine.js**.

---

## 📸 Fitur Utama

| Fitur                     | Keterangan                                                                         |
| ------------------------- | ---------------------------------------------------------------------------------- |
| 📊 Dashboard Analytics    | KPI saldo, pemasukan/pengeluaran, grafik tren 6 bulan, perbandingan per kelas      |
| 👥 Manajemen Master Data  | CRUD Siswa, Kelas, Tahun Ajaran, Kategori Kas                                      |
| 💵 Tagihan & Pembayaran   | Buat tagihan per kelas, catat pembayaran tunai/transfer, verifikasi bukti bayar    |
| 💸 Pencatatan Pengeluaran | Catat pengeluaran kas + upload nota/bukti                                          |
| 📄 Laporan & Export       | Buku mutasi kas dengan saldo berjalan, rekap tunggakan — export ke **Excel & PDF** |
| 📱 Portal Siswa           | Cek tagihan pribadi, upload bukti transfer, lihat transparansi kas kelas           |
| 🔐 Keamanan               | Role-Based Access (Admin, Bendahara Kelas, Siswa), Policy/Gate, custom error pages |

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.x + Laravel
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Database:** MySQL / MariaDB
- **Export:** Maatwebsite Excel + Barryvdh DomPDF
- **Charts:** Chart.js (CDN)

---

## 🖥️ Panduan Instalasi Sesuai Sistem Operasi

Pilih panduan instalasi sesuai sistem operasi yang kamu gunakan:

- [🚀 Cara Instalasi di Windows (Laragon / XAMPP)](#-cara-instalasi-di-windows)
- [🐧 Cara Instalasi di Linux (Ubuntu / Debian / Arch / Fedora)](#-cara-instalasi-di-linux)

---

## 🚀 Cara Instalasi di Windows

### ⚙️ Persyaratan Sistem (Windows)

- **PHP** ≥ 8.2 (dengan ekstensi: `zip`, `gd`, `mbstring`, `pdo_mysql`)
- **Composer** (PHP Package Manager)
- **MySQL** (direkomendasikan menggunakan [Laragon](https://laragon.org/))
- **Node.js** ≥ 18 + **npm**
- **Git**

### Step-by-Step (Windows):

1. **Clone Repository:**

    ```cmd
    git clone https://github.com/ilhampram/KasSekolah.git
    cd KasSekolah
    ```

2. **Install PHP & Frontend Dependencies:**

    ```cmd
    composer install
    npm install
    ```

3. **Environment Setup:**

    ```cmd
    copy .env.example .env
    php artisan key:generate
    ```

4. **Konfigurasi `.env` (Database):**
   Buat database `db_uang_kas_sekolah` di phpMyAdmin, lalu sesuaikan `.env`:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_uang_kas_sekolah
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Migrate, Seed & Storage Link:**

    ```cmd
    php artisan migrate --seed
    php artisan storage:link
    npm run build
    ```

6. **Jalankan Aplikasi:**
    ```cmd
    php artisan serve
    ```
    Buka browser di: **http://localhost:8000**

---

## 🐧 Cara Instalasi di Linux

### ⚙️ Persyaratan Sistem (Linux)

- **PHP** ≥ 8.2 (`php-cli`, `php-fpm`, `php-mysql`, `php-xml`, `php-mbstring`, `php-curl`, `php-zip`, `php-gd`, `php-intl`)
- **Composer**
- **MySQL / MariaDB Server**
- **Node.js** ≥ 18 + **npm**
- **Git**

---

### 1. Persiapan Environment & Install System Dependencies

#### 🔹 Ubuntu / Debian / Mint:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
composer mariadb-server nodejs npm git
```

#### 🔹 Arch Linux / Manjaro:

```bash
sudo pacman -Syu --needed php php-fpm composer mariadb nodejs npm git
```

#### 🔹 Fedora / RHEL:

```bash
sudo dnf install -y php php-cli php-fpm php-mysqlnd php-xml php-mbstring \
php-curl php-zip php-gd php-intl composer mariadb-server nodejs npm git
```

---

### 2. Konfigurasi Database MySQL / MariaDB

Nyalakan service database dan buat user khusus:

```bash
# Jalankan service database
sudo systemctl enable --now mariadb # atau 'mysql' di Ubuntu

# Masuk ke prompt MariaDB/MySQL
sudo mysql -u root
```

Eksekusi perintah SQL berikut:

```sql
CREATE DATABASE db_uang_kas_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kas_user'@'localhost' IDENTIFIED BY 'password_aman';
GRANT ALL PRIVILEGES ON db_uang_kas_sekolah.* TO 'kas_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### 3. Clone Repository & Install Dependencies

```bash
git clone https://github.com/ilhampram/KasSekolah.git
cd KasSekolah

# Install Composer & Node dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader
npm install
```

---

### 4. Konfigurasi Environment (`.env`)

```bash
cp .env.example .env
php artisan key:generate
nano .env
```

Sesuaikan bagian database sesuai kredensial yang dibuat pada Langkah 2:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_uang_kas_sekolah
DB_USERNAME=kas_user
DB_PASSWORD=password_aman
```

---

### 5. Migration, Seeder & Folder Permissions

Atur permission folder `storage` dan `bootstrap/cache` agar dapat ditulis oleh web server/user:

```bash
# Jalankan migrasi database
php artisan migrate --seed

# Buat symbolic link storage
php artisan storage:link

# Atur hak akses permission Linux
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Build asset Tailwind CSS
npm run build
```

---

### 6. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser dan akses: **http://localhost:8000** atau **http://127.0.0.1:8000**

---

## 🌐 Opsi Production Server di Linux (Nginx + PHP-FPM)

Jika aplikasi hendak di-deploy ke VPS Linux menggunakan **Nginx**:

1. Buat konfigurasi Nginx:

    ```bash
    sudo nano /etc/nginx/sites-available/kassekolah
    ```

2. Tambahkan block konfigurasi berikut:

    ```nginx
    server {
        listen 80;
        server_name kassekolah.local;
        root /var/www/KasSekolah/public;

        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";

        index index.php;
        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
    ```

3. Aktifkan rute virtualhost & reload Nginx:
    ```bash
    sudo ln -s /etc/nginx/sites-available/kassekolah /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl restart nginx php8.2-fpm
    ```

---

## 🔑 Akun Demo

Setelah melakukan seeding data, gunakan akun berikut untuk login:

| Role                         | Email                | Password   |
| ---------------------------- | -------------------- | ---------- |
| 👑 Admin / Bendahara Sekolah | `admin@sekolah.test` | `password` |
| 👨‍🎓 Siswa 1                   | `andi@siswa.test`    | `password` |
| 👨‍🎓 Siswa 2                   | `citra@siswa.test`   | `password` |

---

## 📖 Cara Menggunakan Aplikasi

### 🔹 Sebagai Admin / Bendahara Sekolah

1. **Login** menggunakan akun Admin.
2. **Dashboard** — Tinjau ringkasan saldo, statistik pemasukan, pengeluaran, serta grafik arus kas.
3. **Master Data** — Kelola data Tahun Ajaran, Kelas, Data Siswa, dan Kategori Kas.
4. **Tagihan Siswa** — Buat alokasi tagihan kas secara publik/per kelas.
5. **Kasir & Pembayaran** — Entri pembayaran tunai siswa atau verifikasi bukti bayar online.
6. **Transaksi Kas** — Input pencatatan pengeluaran kas dilengkapi foto nota/lampiran.
7. **Laporan Keuangan** — Cetak mutasi kas & rekap tunggakan siswa ke format **Excel / PDF**.

### 🔹 Sebagai Siswa

1. **Login** menggunakan akun Siswa.
2. **Dashboard** — Cek status keterlibatan kas & kewajiban tagihan pribadi.
3. **Upload Bukti Transfer** — Unggah foto struk/resi transfer untuk verifikasi pembayaran online.
4. **Transparansi Kas** — Akses laporan terbuka pemasukan & pengeluaran kas kelas secara real-time.

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

| Role                | Hak Akses                                                                   |
| ------------------- | --------------------------------------------------------------------------- |
| **Admin**           | Full access: master data, tagihan, pembayaran, pengeluaran, laporan, export |
| **Bendahara Kelas** | Kelola tagihan & kas di kelas yang ditugaskan                               |
| **Siswa**           | Lihat tagihan pribadi, upload bukti bayar, lihat transparansi kas           |

---

## 🐛 Troubleshooting

| Masalah                                      | Solusi                                                                                  |
| -------------------------------------------- | --------------------------------------------------------------------------------------- |
| `Permission denied` pada `storage/` (Linux)  | Jalankan `sudo chown -R $USER:www-data storage bootstrap/cache && chmod -R 775 storage` |
| `SQLSTATE[HY000] [1049] Unknown database`    | Buat database `db_uang_kas_sekolah` melalui MariaDB CLI / phpMyAdmin                    |
| `Access denied for user 'root'@'localhost'`  | Di Linux, MySQL root menggunakan `auth_socket`. Gunakan user DB non-root                |
| `Vite manifest not found`                    | Jalankan `npm run build`                                                                |
| `The stream or file ... could not be opened` | Jalankan `php artisan storage:link`                                                     |
| `Class not found`                            | Jalankan `composer dump-autoload`                                                       |
| Export Excel / GD error                      | Pastikan ekstensi `php-zip` dan `php-gd` aktif (`sudo apt install php-zip php-gd`)      |

---

## 📝 Lisensi

Proyek ini dibuat untuk keperluan edukasi dan tugas sekolah.

---

> Dibuat dengan ❤️ menggunakan Laravel + Tailwind CSS
