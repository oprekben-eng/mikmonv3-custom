<div align="center">
  <img src="https://raw.githubusercontent.com/laksa19/laksa19.github.io/master/img/mikhmon.png" alt="Mikhmon Custom Badge" height="120">
</div>

# Mikhmon V3 (Exclusive Custom Edition)
![Mikhmon Version](https://img.shields.io/badge/Version-v3.0.Custom-blue) ![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.x-purple) ![RouterOS](https://img.shields.io/badge/RouterOS-v6%20|%20v7%20Support-orange)

Ini adalah repositori sumber khusus dari **Mikhmon V3** (MikroTik Hotspot Monitor) yang telah diperbarui dan dimodifikasi secara besar-besaran dengan berbagai fitur _Enterprise_ khusus yang tidak ada di versi aslinya. 

Aplikasi ini ditujukan untuk mempermudah operasional bisnis ISP skala kecil menengah (RT/RW Net), agen _reseller_ voucher, serta manajemen penagihan bulanan sistem PPoE dengan otomatisasi WhatsApp.

---

## 🚀 Fitur Unggulan (Custom Features)

### 1. Sistem Portal Agen (Reseller Tiers)
Agen Anda tidak perlu masuk ke halaman Admin. Mereka kini memiliki antarmuka Portal *(Dashboard)* khusus yang terpisah.
- Form **Generate Voucher** instan.
- Halaman **My Vouchers** dengan tampilan Grid keren (beserta fitur *Copy to Clipboard* sekali klik).
- **Saldo Top-Up**: Agen tidak bisa mencetak voucher melebihi batas saldo mereka.
- Harga margin jual dan beli bisa disesuaikan secara dinamis oleh Admin untuk masing-masing agen per-paket profil.

### 2. Integrasi Pengingat & Pengirim Voucher via WhatsApp (Fonnte API)
Kirim detail *user/password* Voucher hotspot langsung ke WhatsApp pelanggan hanya dengan menekan satu tombol.
- Integrasi langsung API Fonnte.
- Kalimat Template WhatsApp (contoh: Sapaan, Harga, Paket) dapat dikustomisasi sebebas mungkin.

### 3. Sistem Manajemen Tagihan Bulanan (Standalone)
Modul baru untuk mencatat *Billing* pelanggan tetap Anda, memisahkan data tanpa bergantung pada penyimpanan sistem Comment MikroTik API lagi!
- Penyimpanan lokal database yang ringan & terpadu.
- **Auto-Reminder Cron Job**: Skrip terintegrasi yang mengirimkan pesan pengingat tagihan WhatsApp otomatis setiap hari sesuai tanggal jatuh tempo.

### 4. Sistem "Auto-Update" Aplikasi terintegrasi
Mengelola versi ini tidak perlu repot *upload* FTP. Anda cukup klik **Update Aplikasi** dari antarmuka Web. Mikhmon secara otomatis akan:
- Mengunduh (download) ZIP pembaruan dari URL repositori GitHub ini.
- Melakukan pengecekan File (Menghindari penimpaan file konfigurasi *password* RouterOS/Database rahasia Anda).
- Merestrukturisasi direktori _source code_ secara _Live_ (termasuk menjalankan pembaruan struktur DB *schema.sql*).

### 5. Installer Otomatis Bebas Rumit (Web Installer)
Halaman Instalasi Database cerdas (akses pertama instalasi akan diarahkan secara dinamis ke installer) yang akan memasang kerangka PDO Sqlite / MySQL Anda dalam 3 langkah mudah.

---

## �️ Panduan Instalasi di Server/Hosting
Aplikasi ini sudah dilengkapi dengan sistem **Web Installer Otomatis** untuk memudahkan Anda.

1. **Upload ke Hosting**: Buka cPanel server Anda, masuk ke *File Manager > public_html*, lalu unggah file `.zip` dari _Source Code_ Mikhmon Custom ini.
2. **Ekstrak**: Ekstrak isi file zip tersebut ke dalam direktori aplikasi (misal `public_html/mikhmon/`).
3. **Database Baru**: Buka menu *MySQL® Databases* di cPanel, buat _database_ baru beserta _User_-nya (simpan nama DB, Username, dan Password).
4. **Instalasi Web**: Akses URL aplikasi Anda dari Browser (contoh: `https://domain-anda.com/mikhmon/`). Halaman instalasi akan langsung menyambut Anda.
5. **Konfigurasi Akhir**: Masukkan detail _Database_ yang baru saja dibuat di langkah 3 ke form instalator. Sistem akan otomatis memasang _table_ dan akun Admin khusus untuk Anda!

### 🔄 Cara Install Fitur Auto-Update di Hosting Lama
Jika aplikasi Mikhmon Anda sudah telanjur berada di _hosting_ dan Anda tidak memiliki fitur menu **Update Aplikasi**, silakan lakukan **Bootstrap Update** sekali saja dengan cara:
1. *Download* file [`update.php`](https://raw.githubusercontent.com/oprekben-eng/mikmonv3-custom/main/update.php) (Klik kanan > *Save as*).
2. Unggah file tersebut ke dalam folder *root* instalasi Mikhmon Anda di cPanel (sejajar dengan file `admin.php` dan `index.php`).
3. Akses file tersebut via *browser* Anda (Contoh: `https://domainanda.com/mikhmon/update.php`).
4. Tunggu beberapa detik hingga proses instalasi pembaruan dan log sukses tampil di layar. Mulai saat ini dan seterusnya, Anda bisa memakai tombol **System > Update Aplikasi** di dalam layar Admin.

---

## �📦 Penyesuaian RouterOS v7 & Stabilitas
Sistem juga telah dioptimalkan agar sepenuhnya mendukung **MikroTik RouterOS v7**:
- Normalisasi penanggalan/jam (ROS v7 parsing).
- Pencegahan _Blank Page/Error Array_ melalui perbaikan metode pengambilan array variabel MIKROTIK.
- Penghitungan dan visualisasi Trafik (Tx/Rx) di layar Dashboard.

---

## 📝 Setup & Konfigurasi Ekstra (CronJob)

Untuk fitur **Pengingat Penagihan Otomatis**, Anda memerlukan Cronjob di (cPanel/Hosting OS Linux):

```bash
# Menjalankan Skrip Pengingat Penagihan Setiap Hari:
/usr/local/bin/php /home/USERNAME_ANDA/public_html/process/cron_billing.php >/dev/null 2>&1
```

*(Keterangan: Ganti path sesuai server masing-masing, dan set waktu cronjob _Once Per Day_ pada jam pagi hari).*

---

## Hak Cipta & Orisinalitas
*Basis _Core_ Project oleh: **Laksamadi Guko**.*  
*Dimodifikasi (Custom Web App Edition) untuk penyesuaian khusus bisnis ISP MikroTik terpadu oleh **Oprekben Engineering (Pemilik Repositori ini)***.
