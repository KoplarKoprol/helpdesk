# Helpdesk Tiket IT

Aplikasi helpdesk tiket IT dengan RBAC (admin, teknisi, user), dibangun native PHP dengan pola MVC.

## Struktur Folder

```
helpdesk/
├── app/
│   ├── Controllers/     # Logika aplikasi (AuthController, AdminController, TicketController)
│   ├── Models/          # Interaksi database (User, Role, Ticket)
│   └── Views/           # Tampilan per role (auth, admin, teknisi, user)
├── config/
│   └── database.php     # Koneksi PDO ke MySQL
├── core/
│   ├── Router.php       # Routing sederhana
│   ├── Controller.php   # Base controller (view, redirect, cek role)
│   └── Model.php        # Base model (koneksi db)
├── public/
│   ├── index.php        # Entry point
│   ├── css/, js/
│   └── uploads/         # Lampiran tiket
├── routes/
│   └── web.php          # Daftar route
└── helpdesk_ddl.sql      # Skema database
```

## Setup

1. Import `helpdesk_ddl.sql` ke MySQL.
2. Sesuaikan kredensial di `config/database.php`.
3. Jalankan `composer install` di root folder untuk mengunduh PhpSpreadsheet, PHPMailer, dan Dompdf.
4. Sesuaikan kredensial SMTP di `config/mail.php` untuk fitur notifikasi email.
5. Arahkan document root ke folder `public/`.
6. Buat user awal manual lewat seeder/insert langsung untuk akun admin pertama, selanjutnya perubahan role dilakukan lewat dashboard admin.

## Library Eksternal

- **PHPMailer** (via Composer) — notifikasi email saat tiket dibuat, status berubah, dan assign teknisi.
- **PhpSpreadsheet** (via Composer) — export laporan tiket ke `.xlsx` melalui `/reports/export-excel`, hanya bisa diakses admin.
- **Dompdf** (via Composer) — cetak detail tiket + riwayat komentar ke `.pdf` melalui `/tickets/{id}/export-pdf`, bisa diakses semua role yang berhak lihat tiket.

## CRUD per Entitas

- **Roles**: read-only (di-seed dari DDL, tidak ada UI ubah/hapus karena role bersifat tetap: admin, teknisi, user).
- **Users**: create (admin & register publik), read, update (ubah role), delete (admin, ditolak kalau user masih punya tiket terkait).
- **Categories**: full CRUD oleh admin di `/categories`.
- **Tickets**: full CRUD oleh user (pemilik), plus update status & assign teknisi oleh teknisi/admin.
- **Comments**: create, read, update, delete — hanya oleh penulis komentar (delete juga bisa oleh admin).
- **Attachments**: create (saat buat tiket), read, delete — oleh pemilik tiket atau admin (turut menghapus file fisik).

## Alur RBAC

- Role tersimpan di tabel `roles` (admin, teknisi, user), direferensikan oleh `users.role_id`.
- Admin login ke `/admin/dashboard`, melihat daftar seluruh user beserta role-nya.
- Admin memilih role baru lewat dropdown pada tabel dan submit form ke `/admin/users/update-role`.
- `AdminController::updateUserRole()` memanggil `User::updateRole()` untuk update `role_id` di database, tanpa perlu akses database manual.
