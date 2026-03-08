# Panduan Instalasi Mikhmon di Hosting

## Persyaratan Hosting

| Komponen | Minimum |
|----------|---------|
| PHP | >= 7.4 |
| MySQL / MariaDB | >= 5.7 |
| Ekstensi PHP | PDO MySQL, cURL, Mbstring, JSON |
| Disk | >= 50 MB |
| SSL | Disarankan (HTTPS) |

> **Catatan:** Hosting harus bisa menghubungi IP MikroTik (API port 8728). Pastikan port API terbuka dari sisi MikroTik dan tidak diblokir oleh firewall hosting.

---

## Langkah 1: Persiapan File

1. **Zip semua file** project Mikhmon di komputer lokal:
   ```
   mikhmon/
   ├── admin.php
   ├── index.php
   ├── install.php
   ├── database/
   ├── agent/
   ├── css/
   ├── dashboard/
   ├── hotspot/
   ├── include/
   ├── js/
   ├── lang/
   ├── lib/
   ├── ppp/
   ├── process/
   ├── report/
   ├── settings/
   ├── status/
   ├── system/
   ├── traffic/
   └── voucher/
   ```

2. **File yang TIDAK perlu diupload** (opsional, untuk keamanan):
   - `docker-compose.yml`
   - `nginx.conf`
   - `_config.yml`
   - `.profile`
   - `temp.txt`
   - `README.md`

---

## Langkah 2: Upload ke Hosting

### Via cPanel File Manager:
1. Login ke **cPanel** hosting
2. Buka **File Manager**
3. Masuk ke folder `public_html` (atau subdomain yang diinginkan)
4. Klik **Upload** → pilih file `.zip`
5. Setelah upload selesai, klik kanan → **Extract**

### Via FTP (FileZilla):
1. Buka **FileZilla** atau FTP client lain
2. Masukkan:
   - Host: `ftp.domainanda.com`
   - Username: username FTP
   - Password: password FTP
   - Port: `21`
3. Upload semua file ke folder `public_html/`
   - Atau ke subfolder jika ingin di subdirektori: `public_html/mikhmon/`

---

## Langkah 3: Buat Database MySQL

### Via cPanel:
1. Buka **MySQL® Databases** di cPanel
2. **Create New Database**: `mikhmon_agent` (atau nama lain)
3. **Create New User**: buat username dan password
4. **Add User to Database**: pilih user → pilih database → centang **ALL PRIVILEGES** → klik **Make Changes**

### Catat informasi berikut:
```
Host     : localhost  (biasanya default di hosting)
Username : cpanel_username_dbuser
Password : password_yang_dibuat
Database : cpanel_username_mikhmon_agent
```

> **Penting:** Di cPanel, nama database dan user biasanya diawali prefix username cPanel. Contoh: `alfa96_mikhmon_agent`

---

## Langkah 4: Jalankan Web Installer

1. Buka browser, akses:
   ```
   https://domainanda.com/install.php
   ```
   Atau jika di subfolder:
   ```
   https://domainanda.com/mikhmon/install.php
   ```

2. **Step 1 - Pengecekan Server:**
   - Pastikan semua item bertanda ✅ **OK**
   - Jika ada yang ❌ **FAILED**, hubungi provider hosting untuk mengaktifkan ekstensi PHP yang kurang
   - Klik **Lanjutkan**

3. **Step 2 - Konfigurasi Database:**
   - Isi informasi database yang dicatat di Langkah 3
   - Klik **Install Sekarang**

4. **Step 3 - Selesai:**
   - Jika berhasil, file `install.php` akan otomatis dihapus
   - Jika tidak terhapus otomatis, **hapus manual** via File Manager
   - Klik **Buka Aplikasi** untuk masuk ke halaman login

---

## Langkah 5: Konfigurasi MikroTik

### Buka API Port di MikroTik:
```
/ip service set api disabled=no port=8728
```

### Buat user API di MikroTik:
```
/user add name=mikhmon group=full password=passwordapi
```

### Pastikan firewall mengizinkan koneksi dari IP hosting:
```
/ip firewall filter add chain=input protocol=tcp dst-port=8728 src-address=IP_HOSTING action=accept comment="Allow Mikhmon API"
```

> **Penting:** Ganti `IP_HOSTING` dengan IP server hosting Anda. Bisa dicek di informasi hosting atau tanya CS provider.

---

## Langkah 6: Login & Tambah Session

1. Akses `https://domainanda.com/admin.php`
2. Login dengan user/password default Mikhmon
3. Tambah session baru:
   - **Session Name**: nama bebas (contoh: `router1`)
   - **IP MikroTik**: IP publik MikroTik atau IP yang bisa diakses dari hosting
   - **Username**: username API MikroTik (contoh: `mikhmon`)
   - **Password**: password API MikroTik
   - **Port**: `8728` (default API)
4. Klik **Connect**

---

## Langkah 7: Pengaturan Otomasi Tagihan WA (Cron Job)

Untuk menjalankan fitur pengiriman tagihan otomatis pppoe via WA setiap hari:

### Konfigurasi di cPanel
1. Login ke cPanel hosting Anda
2. Cari menu **Cron Jobs** di bagian "Advanced"
3. Pada opsi **Common Settings**, pilih **Once Per Day** (Sekali sehari, misal jam 08:00)
4. Di kolom **Command**, masukkan perintah berikut:
   ```bash
   /usr/local/bin/php /home/username_cpanel/public_html/process/cron_billing.php >/dev/null 2>&1
   ```
   > **Catatan Penting**: 
   > - Ganti `username_cpanel` dengan username asli cPanel Anda.
   > - Sesuaikan path `/public_html/` jika menginstallnya di subfolder (misal `/public_html/mikhmon/process/...`)
   > - Jika server hosting memblokir path PHP CLI default, Anda bisa gunakan curl sebagai alternatif: 
   >   `curl -s "https://domain-anda.com/process/cron_billing.php" >/dev/null 2>&1`
5. Klik **Add New Cron Job**

---

## Troubleshooting

### ❌ Tidak bisa connect ke MikroTik
- Pastikan **API port (8728)** terbuka di MikroTik
- Pastikan **IP hosting** diizinkan di firewall MikroTik
- Cek apakah hosting mendukung koneksi **outbound TCP port 8728**
- Beberapa hosting shared **memblokir** koneksi outbound non-standar — hubungi provider

### ❌ Error saat install database
- Pastikan nama database, username, dan password **sudah benar** (perhatikan prefix cPanel)
- Pastikan user sudah di-assign ke database dengan **ALL PRIVILEGES**

### ❌ Halaman blank / error 500
- Cek versi PHP di cPanel → **Select PHP Version** → pilih PHP 7.4 atau lebih baru
- Aktifkan ekstensi: `pdo_mysql`, `curl`, `mbstring`, `json`
- Cek error log di cPanel → **Error Log**

### ❌ File install.php tidak terhapus otomatis
- Hapus manual via **File Manager** di cPanel
- **WAJIB dihapus** setelah instalasi selesai demi keamanan!

---

## Struktur URL Setelah Instalasi

| Halaman | URL |
|---------|-----|
| Login Admin | `https://domain.com/admin.php` |
| Dashboard | `https://domain.com/?session=nama_session` |
| Portal Agen | `https://domain.com/agent/` |
| Web Installer | `https://domain.com/install.php` (hapus setelah install!) |

---

## Tips Keamanan

1. **Hapus `install.php`** setelah instalasi
2. **Gunakan HTTPS** (SSL) — bisa pakai Let's Encrypt gratis di cPanel
3. **Ganti password default** admin Mikhmon
4. **Batasi akses API** MikroTik hanya dari IP hosting
5. **Backup database** secara berkala via cPanel → **Backup Wizard**
