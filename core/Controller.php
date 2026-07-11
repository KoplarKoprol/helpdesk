<?php

/**
 * Base Controller.
 *
 * Menyediakan fungsi umum yang dipakai semua controller:
 * render view, redirect, flash message, dan pengecekan role (RBAC).
 */
class Controller
{
    /**
     * Merender file view dan mengirim data ke dalamnya.
     *
     * @param string $path Path view relatif terhadap app/Views, tanpa ekstensi .php
     * @param array $data Data yang akan di-extract jadi variabel di view
     */
    protected function view($path, $data = [])
    {
        extract($data);
        require __DIR__ . '/../app/Views/' . $path . '.php';
    }

    /**
     * Redirect ke path tertentu, otomatis ditempel dengan BASE_PATH.
     *
     * @param string $path Path tujuan, contoh: '/login'
     */
    protected function redirect($path)
    {
        header('Location: ' . url($path));
        exit;
    }

    /**
     * Menyimpan pesan flash yang akan tampil sekali di halaman berikutnya.
     *
     * @param string $message Isi pesan
     * @param string $type 'success' atau 'error'
     */
    protected function flash($message, $type = 'success')
    {
        setFlash($message, $type);
    }

    /**
     * Memastikan user sudah login dan (opsional) memiliki role yang diizinkan.
     * Menghentikan eksekusi (403) kalau role tidak sesuai.
     *
     * @param array $roles Daftar role yang diizinkan, kosong berarti hanya cek login
     */
    protected function requireRole($roles = [])
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        if (!empty($roles) && !in_array($_SESSION['user']['role'], $roles)) {
            http_response_code(403);
            echo 'Akses ditolak.';
            exit;
        }
    }
}
