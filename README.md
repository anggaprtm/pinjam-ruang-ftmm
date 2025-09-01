# Aplikasi Peminjaman Ruang FTMM 🏫
Aplikasi Peminjaman Ruang adalah sistem berbasis web yang dirancang untuk memfasilitasi proses peminjaman ruangan di lingkungan Fakultas Teknologi Maju dan Multidisiplin (FTMM) Universitas Airlangga. 🚀 Aplikasi ini memungkinkan mahasiswa dan dosen untuk melihat jadwal penggunaan ruang, mengajukan peminjaman, dan mengelola status peminjaman secara efisien.

## ✨ Fitur Utama
- 🚪 **Manajemen Ruangan**: Kelola data ruangan yang tersedia untuk dipinjam, termasuk informasi kapasitas, fasilitas, dan status ketersediaan.
- 📚 **Jadwal Perkuliahan**: Impor dan kelola jadwal perkuliahan untuk menghindari bentrok dengan kegiatan peminjaman.
- 📝 **Peminjaman Ruang**: Pengguna dapat mengajukan peminjaman ruang dengan mengisi formulir yang berisi informasi kegiatan, waktu, dan kebutuhan lainnya.
- 📅 **Kalender Peminjaman**: Tampilan kalender interaktif untuk melihat jadwal penggunaan seluruh ruangan secara real-time.
- 📧 **Notifikasi Email**: Sistem notifikasi otomatis melalui email untuk setiap tahapan proses peminjaman (pengajuan, persetujuan, penolakan).
- 👥 **Manajemen Pengguna**: Sistem pengelolaan pengguna dengan beberapa level akses (admin, mahasiswa, dosen).
- 📎 **Verifikasi Berkas**: Proses verifikasi surat izin kegiatan oleh admin untuk menyetujui atau menolak peminjaman.
- 📜 **Riwayat Peminjaman**: Lacak dan kelola riwayat peminjaman yang pernah dilakukan oleh setiap pengguna.

## 🛠️ Teknologi yang Digunakan
+ **Backend**: Laravel Framework 10
+ **Frontend**: Bootstrap, jQuery, fullcalendar.io
+ **Database**: MySQL
+ **Server**: Apache/Nginx

## 🚀 Instalasi dan Konfigurasi
1. **Clone Repository**
2. **Install Dependencies**

    Pastikan Anda memiliki  dan  terinstal.

3. **Konfigurasi Lingkungan ⚙️**

    Salin berkas `.env.example` menjadi `.env` dan sesuaikan konfigurasinya, terutama untuk koneksi database.

    Setelah itu, jalankan perintah berikut untuk menghasilkan kunci aplikasi:

4. **Migrasi dan Seeding Database 🗄️**

    Jalankan migrasi untuk membuat tabel-tabel yang dibutuhkan dan seeder untuk mengisi data awal.

5. **Jalankan Aplikasi ▶️**

    Jalankan server pengembangan bawaan Laravel.

    Aplikasi akan berjalan di `http://127.0.0.1:8000`.

## 📁 Struktur Proyek
Proyek ini mengikuti struktur standar dari Laravel. Berikut adalah beberapa direktori penting:

+ `app/Http/Controllers`: Berisi controller yang mengatur logika aplikasi.
+ `app/Models`: Berisi model-model Eloquent untuk berinteraksi dengan database.
+ `database/migrations`: Berisi skema database.
+ `resources/views`: Berisi file-file blade template untuk tampilan antarmuka.
+ `routes/web.php`: Berisi definisi rute-rute aplikasi web.
+ `public`: Document root untuk aplikasi, berisi aset-aset publik.

## 👋 Kontribusi
Kontribusi dari siapa pun sangat kami harapkan. Jika Anda menemukan bug atau memiliki ide untuk fitur baru, silakan buat issue atau ajukan pull request. Kami sangat menghargai setiap kontribusi Anda! ❤️