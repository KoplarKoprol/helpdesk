<?php

/**
 * Validator.
 *
 * Kumpulan fungsi validasi input yang dipakai berulang di berbagai
 * controller: format email, whitelist nilai enum, dan batas panjang teks.
 * Semua method bersifat static supaya bisa dipanggil langsung tanpa
 * instansiasi, karena sifatnya stateless (murni fungsi pengecekan).
 */
class Validator
{
    /**
     * Memvalidasi format email.
     *
     * @param string $email
     * @return bool
     */
    public static function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Memastikan nilai ada di dalam daftar pilihan yang diizinkan.
     * Dipakai untuk field seperti status, priority, role_id supaya
     * user tidak bisa mengirim nilai sembarangan lewat request manual.
     *
     * @param mixed $value
     * @param array $allowed
     * @return bool
     */
    public static function inList($value, array $allowed)
    {
        return in_array($value, $allowed, true);
    }

    /**
     * Memvalidasi panjang teks berada dalam rentang tertentu.
     *
     * @param string $text
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function lengthBetween($text, $min, $max)
    {
        $length = mb_strlen(trim($text));
        return $length >= $min && $length <= $max;
    }

    /**
     * Memvalidasi format nomor telepon Indonesia sederhana:
     * hanya angka (boleh diawali '+'), panjang 9-15 digit.
     *
     * @param string $phone
     * @return bool
     */
    public static function isPhoneNumber($phone)
    {
        return preg_match('/^\+?[0-9]{9,15}$/', $phone) === 1;
    }
}
