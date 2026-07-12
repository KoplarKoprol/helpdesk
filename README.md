# Helpdesk Tiket IT

Aplikasi helpdesk tiket IT dengan RBAC (admin, teknisi, user), dibangun native PHP dengan pola MVC.

## Struktur Folder

```
helpdesk/
├── app/
│   ├── Controllers/     # AdminController, AuthController, CategoryController,
│   │                    # ReportController, TicketController
│   ├── Models/          # Attachment, Category, Comment, Role, Ticket, User
│   ├── Services/        # ExportService, MailService, PdfService, Validator
│   └── Views/
│       ├── admin/       # create_user.php, dashboard.php
│       ├── auth/        # login.php, register.php
│       ├── categories/  # create.php, edit.php, index.php
│       ├── partials/    # flash.php
│       └── tickets/     # create.php, edit.php, index.php, pdf.php, show.php
├── config/
│   ├── database.php     # Koneksi PDO ke MySQL
│   └── mail.php         # Kredensial SMTP untuk PHPMailer
├── core/
│   ├── Router.php       # Routing sederhana
│   ├── Controller.php   # Base controller (view, redirect, cek role)
│   └── Model.php        # Base model (koneksi db)
├── public/
│   ├── index.php        # Entry point (document root diarahkan ke sini)
│   ├── .htaccess
│   └── uploads/         # Lampiran tiket (harus writable oleh web server)
├── routes/
│   └── web.php          # Daftar route
├── index.php             # Redirect shortcut ke /public/login (opsional, bukan entry point utama)
└── helpdesk_db.sql       # Skema database
```

> **Catatan:** tidak ada folder per-role (`teknisi/`, `user/`) di `Views` — role hanya menentukan hak akses lewat pengecekan role di controller, bukan folder tampilan terpisah. Halaman tiket (`tickets/`) dipakai bersama oleh semua role, dengan elemen yang tampil disesuaikan lewat kondisi di view (mis. tombol "Hapus" hanya untuk pemilik tiket atau admin).

## Setup

1. Import `helpdesk_db.sql` ke MySQL.
2. Sesuaikan kredensial di `config/database.php`.
3. Jalankan `composer install` di root folder untuk mengunduh PhpSpreadsheet, PHPMailer, dan Dompdf.
4. Sesuaikan kredensial SMTP di `config/mail.php` untuk fitur notifikasi email.
5. Arahkan document root ke folder `public/`.
6. Buat user awal manual lewat seeder/insert langsung untuk akun admin pertama, selanjutnya perubahan role dilakukan lewat dashboard admin.

## Services

- **MailService** — wrapper PHPMailer, dipakai controller untuk kirim notifikasi email.
- **PdfService** — wrapper Dompdf, dipakai `TicketController` untuk generate PDF detail tiket.
- **ExportService** — wrapper PhpSpreadsheet, dipakai `ReportController` untuk export laporan `.xlsx`.
- **Validator** — helper validasi input form (dipakai di berbagai controller sebelum insert/update ke model).

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