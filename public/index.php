<?php

/**
 * Entry point aplikasi (front controller).
 *
 * Semua request masuk lewat file ini (diarahkan oleh .htaccess),
 * yang bertugas: load semua class, definisikan helper global,
 * lalu menyerahkan request ke Router.
 */

session_start();

/**
 * Token CSRF, dibuat sekali per session dan dipakai untuk
 * memvalidasi bahwa form POST benar-benar berasal dari halaman
 * aplikasi ini, bukan dari situs lain (cross-site request forgery).
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Menghasilkan elemen <input type="hidden"> berisi token CSRF,
 * tinggal ditaruh di dalam setiap <form method="POST">.
 *
 * @return string
 */
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/**
 * Memeriksa apakah token CSRF dari request POST cocok dengan
 * yang tersimpan di session. Dipanggil otomatis oleh Router
 * untuk setiap request POST.
 *
 * @return bool
 */
function verifyCsrf()
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Path dasar aplikasi relatif terhadap domain, dipakai karena
 * aplikasi berjalan di subfolder (bukan document root).
 * Dideteksi otomatis dari lokasi file ini diakses, supaya tidak perlu
 * diubah manual kalau nama folder project berubah (misal saat development).
 * Contoh hasil: '/helpdesk/public' atau '/helpdesksmentara/public'.
 */
define('BASE_PATH', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

/**
 * Menghasilkan URL lengkap dari path relatif, dengan BASE_PATH
 * otomatis ditempel di depan. Dipakai di semua href, form action,
 * dan redirect supaya path tetap benar di subfolder manapun aplikasi berjalan.
 *
 * @param string $path
 * @return string
 */
function url($path = '')
{
    return BASE_PATH . $path;
}

/**
 * Menyimpan pesan flash ke session, untuk ditampilkan sekali
 * di halaman berikutnya setelah redirect.
 *
 * @param string $message
 * @param string $type 'success' atau 'error'
 */
function setFlash($message, $type = 'success')
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Mengambil dan menghapus flash message dari session (tampil sekali saja).
 *
 * @return array|null
 */
function getFlash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';

// Autoload Composer untuk library eksternal: PHPMailer, PhpSpreadsheet, Dompdf
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Role.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Ticket.php';
require_once __DIR__ . '/../app/Models/Comment.php';
require_once __DIR__ . '/../app/Models/Attachment.php';

require_once __DIR__ . '/../app/Services/MailService.php';
require_once __DIR__ . '/../app/Services/ExportService.php';
require_once __DIR__ . '/../app/Services/PdfService.php';
require_once __DIR__ . '/../app/Services/Validator.php';

require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/CategoryController.php';
require_once __DIR__ . '/../app/Controllers/TicketController.php';
require_once __DIR__ . '/../app/Controllers/ReportController.php';

$router = new Router();
require_once __DIR__ . '/../routes/web.php';

$router->dispatch();
