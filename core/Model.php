<?php

/**
 * Base Model.
 *
 * Semua model turunan (User, Ticket, dll) meng-extend class ini
 * agar otomatis mendapat koneksi database lewat $this->db.
 */
class Model
{
    protected $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }
}
