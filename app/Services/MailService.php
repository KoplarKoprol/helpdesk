<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * MailService.
 *
 * Wrapper PHPMailer untuk notifikasi email transaksional
 * (tiket dibuat, status berubah, assign teknisi). Kalau
 * config/mail.php belum ada, service ini otomatis nonaktif
 * (silent skip) supaya CRUD tiket tetap jalan tanpa error.
 */
class MailService
{
    private $config;
    private $enabled = true;

    public function __construct()
    {
        $configPath = __DIR__ . '/../../config/mail.php';

        if (!file_exists($configPath)) {
            $this->enabled = false;
            return;
        }

        $this->config = require $configPath;
    }

    /**
     * Mengirim email lewat SMTP. Gagal kirim tidak menghentikan
     * aplikasi, hanya dicatat ke error log.
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $body HTML body email
     * @return bool
     */
    public function send($toEmail, $toName, $subject, $body)
    {
        if (!$this->enabled) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'];

            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Gagal kirim email: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Notifikasi ke pembuat tiket setelah tiket berhasil dibuat.
     */
    public function notifyTicketCreated($toEmail, $toName, $ticketId, $title)
    {
        $subject = 'Tiket #' . $ticketId . ' Berhasil Dibuat';
        $body = "Halo $toName,<br><br>Tiket Anda dengan judul <b>" . htmlspecialchars($title) . "</b> telah diterima dan berstatus <b>open</b>.<br>Nomor tiket: #$ticketId<br><br>Tim kami akan segera menindaklanjuti.";

        return $this->send($toEmail, $toName, $subject, $body);
    }

    /**
     * Notifikasi ke pembuat tiket setiap kali status tiket berubah.
     */
    public function notifyStatusChanged($toEmail, $toName, $ticketId, $title, $status)
    {
        $subject = 'Update Status Tiket #' . $ticketId;
        $body = "Halo $toName,<br><br>Status tiket <b>" . htmlspecialchars($title) . "</b> (#$ticketId) telah diubah menjadi <b>" . htmlspecialchars($status) . "</b>.";

        return $this->send($toEmail, $toName, $subject, $body);
    }

    /**
     * Notifikasi ke teknisi saat ditugaskan menangani sebuah tiket.
     */
    public function notifyAssigned($toEmail, $toName, $ticketId, $title)
    {
        $subject = 'Tiket Baru Ditugaskan #' . $ticketId;
        $body = "Halo $toName,<br><br>Anda ditugaskan menangani tiket <b>" . htmlspecialchars($title) . "</b> (#$ticketId). Silakan cek dashboard untuk detailnya.";

        return $this->send($toEmail, $toName, $subject, $body);
    }
}
