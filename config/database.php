<?php

/**
 * Konfigurasi & koneksi database MySQL menggunakan PDO.
 */
class Database
{
    private $host = 'localhost';
    private $dbname = 'helpdesk_db';
    private $user = 'root';
    private $pass = '';
    public $conn;

    /**
     * Membuka koneksi PDO ke database.
     *
     * @return PDO
     */
    public function connect()
    {
        $this->conn = new PDO(
            "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
            $this->user,
            $this->pass
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->conn;
    }
}
