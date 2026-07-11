<?php

/**
 * TicketController.
 *
 * Menangani seluruh CRUD tiket, upload lampiran, komentar,
 * perubahan status, dan assign teknisi. Setiap method dibatasi
 * dengan requireRole() sesuai siapa yang boleh melakukan aksi tersebut.
 */
class TicketController extends Controller
{
    /** @var array Ekstensi file yang diizinkan untuk lampiran tiket */
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];

    /** @var array MIME type asli yang sesuai ekstensi di atas, dicek dari isi file (bukan nama file) */
    private $allowedMimes = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** @var int Ukuran maksimal lampiran dalam byte (2MB) */
    private $maxSize = 2 * 1024 * 1024;

    /** @var array Nilai priority yang sah, dipakai untuk whitelist validation */
    private $allowedPriorities = ['low', 'medium', 'high'];

    /** @var array Nilai status yang sah, dipakai untuk whitelist validation */
    private $allowedStatuses = ['open', 'in_progress', 'resolved', 'closed'];

    /**
     * Menampilkan daftar tiket, otomatis terfilter sesuai role:
     * user hanya lihat tiketnya sendiri, teknisi hanya yang di-assign
     * ke dia, admin lihat semua. Mendukung filter status/kategori/kata kunci.
     */
    public function index()
    {
        $this->requireRole(['user', 'teknisi', 'admin']);

        $ticketModel = new Ticket();
        $categoryModel = new Category();
        $role = $_SESSION['user']['role'];
        $userId = $_SESSION['user']['id'];

        $filters = [
            'status' => $_GET['status'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];

        if ($role === 'user') {
            $filters['user_id'] = $userId;
        } elseif ($role === 'teknisi') {
            $filters['teknisi_id'] = $userId;
        }

        $tickets = $ticketModel->search($filters);

        $this->view('tickets/index', [
            'tickets' => $tickets,
            'role' => $role,
            'categories' => $categoryModel->all(),
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan form buat tiket baru. Hanya untuk role user.
     */
    public function create()
    {
        $this->requireRole(['user']);

        $categoryModel = new Category();
        $this->view('tickets/create', ['categories' => $categoryModel->all()]);
    }

    /**
     * Menyimpan tiket baru, memproses lampiran (jika ada),
     * lalu mengirim notifikasi email ke pembuat tiket.
     */
    public function store()
    {
        $this->requireRole(['user']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';

        if (!Validator::lengthBetween($title, 5, 150)) {
            $this->flash('Judul tiket wajib diisi, 5-150 karakter.', 'error');
            $this->redirect('/tickets/create');
        }

        if (!Validator::lengthBetween($description, 10, 5000)) {
            $this->flash('Deskripsi wajib diisi, minimal 10 karakter.', 'error');
            $this->redirect('/tickets/create');
        }

        if (!Validator::isPhoneNumber($contactPhone)) {
            $this->flash('Nomor telepon tidak valid (9-15 digit angka).', 'error');
            $this->redirect('/tickets/create');
        }

        if (!Validator::inList($priority, $this->allowedPriorities)) {
            $this->flash('Prioritas tidak valid.', 'error');
            $this->redirect('/tickets/create');
        }

        $categoryModel = new Category();

        if (!$categoryModel->find($categoryId)) {
            $this->flash('Kategori tidak valid.', 'error');
            $this->redirect('/tickets/create');
        }

        $ticketModel = new Ticket();
        $ticketId = $ticketModel->create($_SESSION['user']['id'], $categoryId, $title, $description, $contactPhone, $priority);

        if (!empty($_FILES['attachment']['name'])) {
            $this->handleUpload($_FILES['attachment'], $ticketId);
        }

        $mailer = new MailService();
        $mailer->notifyTicketCreated(
            $_SESSION['user']['email'],
            $_SESSION['user']['name'],
            $ticketId,
            $title
        );

        $this->flash('Tiket berhasil dibuat.');
        $this->redirect('/tickets/' . $ticketId);
    }

    /**
     * Menampilkan detail tiket beserta komentar dan lampiran.
     * Kalau yang akses admin, ikut disiapkan daftar teknisi untuk assign.
     *
     * @param int $id
     */
    public function show($id)
    {
        $this->requireRole(['user', 'teknisi', 'admin']);

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);

        if (!$ticket) {
            http_response_code(404);
            echo 'Tiket tidak ditemukan.';
            return;
        }

        $commentModel = new Comment();
        $attachmentModel = new Attachment();
        $teknisiList = [];

        if ($_SESSION['user']['role'] === 'admin') {
            $userModel = new User();
            $teknisiList = $userModel->getTeknisiList();
        }

        $this->view('tickets/show', [
            'ticket' => $ticket,
            'comments' => $commentModel->getByTicket($id),
            'attachments' => $attachmentModel->getByTicket($id),
            'teknisiList' => $teknisiList,
        ]);
    }

    /**
     * Menampilkan form edit tiket. Hanya pemilik tiket yang boleh mengakses.
     *
     * @param int $id
     */
    public function edit($id)
    {
        $this->requireRole(['user']);

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);

        if (!$ticket || $ticket['user_id'] != $_SESSION['user']['id']) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        $categoryModel = new Category();
        $this->view('tickets/edit', ['ticket' => $ticket, 'categories' => $categoryModel->all()]);
    }

    /**
     * Menyimpan perubahan tiket. Kepemilikan tiket divalidasi ulang
     * di controller (bukan hanya disembunyikan di UI).
     *
     * @param int $id
     */
    public function update($id)
    {
        $this->requireRole(['user']);

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);

        if (!$ticket || $ticket['user_id'] != $_SESSION['user']['id']) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';

        if (!Validator::lengthBetween($title, 5, 150)) {
            $this->flash('Judul tiket wajib diisi, 5-150 karakter.', 'error');
            $this->redirect('/tickets/' . $id . '/edit');
        }

        if (!Validator::lengthBetween($description, 10, 5000)) {
            $this->flash('Deskripsi wajib diisi, minimal 10 karakter.', 'error');
            $this->redirect('/tickets/' . $id . '/edit');
        }

        if (!Validator::isPhoneNumber($contactPhone)) {
            $this->flash('Nomor telepon tidak valid (9-15 digit angka).', 'error');
            $this->redirect('/tickets/' . $id . '/edit');
        }

        if (!Validator::inList($priority, $this->allowedPriorities)) {
            $this->flash('Prioritas tidak valid.', 'error');
            $this->redirect('/tickets/' . $id . '/edit');
        }

        $categoryModel = new Category();

        if (!$categoryModel->find($categoryId)) {
            $this->flash('Kategori tidak valid.', 'error');
            $this->redirect('/tickets/' . $id . '/edit');
        }

        $ticketModel->update($id, $title, $description, $categoryId, $contactPhone, $priority);

        $this->flash('Tiket berhasil diperbarui.');
        $this->redirect('/tickets/' . $id);
    }

    /**
     * Menghapus tiket. Hanya pemilik tiket yang boleh menghapus.
     *
     * @param int $id
     */
    public function destroy($id)
    {
        $this->requireRole(['user']);

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);

        if (!$ticket || $ticket['user_id'] != $_SESSION['user']['id']) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        $ticketModel->delete($id);
        $this->flash('Tiket berhasil dihapus.');
        $this->redirect('/tickets');
    }

    /**
     * Mengubah status tiket, lalu mengirim notifikasi email
     * ke pembuat tiket. Hanya teknisi/admin yang boleh mengubah status.
     *
     * @param int $id
     */
    public function updateStatus($id)
    {
        $this->requireRole(['teknisi', 'admin']);

        $status = $_POST['status'] ?? '';

        if (!Validator::inList($status, $this->allowedStatuses)) {
            $this->flash('Status tidak valid.', 'error');
            $this->redirect('/tickets/' . $id);
        }

        $ticketModel = new Ticket();
        $ticketModel->updateStatus($id, $status);

        $ticket = $ticketModel->find($id);
        $userModel = new User();
        $pemilik = $userModel->findById($ticket['user_id']);

        $mailer = new MailService();
        $mailer->notifyStatusChanged($pemilik['email'], $pemilik['name'], $id, $ticket['title'], $status);

        $this->flash('Status tiket berhasil diperbarui.');
        $this->redirect('/tickets/' . $id);
    }

    /**
     * Menugaskan teknisi ke tiket, lalu mengirim notifikasi email
     * ke teknisi tersebut. Hanya admin yang boleh assign.
     *
     * @param int $id
     */
    public function assign($id)
    {
        $this->requireRole(['admin']);

        $teknisiId = (int) ($_POST['teknisi_id'] ?? 0);

        $userModel = new User();
        $teknisiList = $userModel->getTeknisiList();
        $validTeknisiIds = array_column($teknisiList, 'id');

        if (!Validator::inList($teknisiId, $validTeknisiIds)) {
            $this->flash('Teknisi tidak valid.', 'error');
            $this->redirect('/tickets/' . $id);
        }

        $ticketModel = new Ticket();
        $ticketModel->assignTeknisi($id, $teknisiId);

        $ticket = $ticketModel->find($id);
        $teknisi = $userModel->findById($teknisiId);

        $mailer = new MailService();
        $mailer->notifyAssigned($teknisi['email'], $teknisi['name'], $id, $ticket['title']);

        $this->flash('Teknisi berhasil ditugaskan.');
        $this->redirect('/tickets/' . $id);
    }

    /**
     * Menambahkan komentar/riwayat pada tiket. Bisa diisi oleh
     * pembuat tiket, teknisi, maupun admin yang terlibat.
     *
     * @param int $id
     */
    public function comment($id)
    {
        $this->requireRole(['user', 'teknisi', 'admin']);

        $message = trim($_POST['message'] ?? '');

        if (!Validator::lengthBetween($message, 1, 1000)) {
            $this->flash('Komentar tidak boleh kosong dan maksimal 1000 karakter.', 'error');
            $this->redirect('/tickets/' . $id);
        }

        $commentModel = new Comment();
        $commentModel->create($id, $_SESSION['user']['id'], $message);
        $this->flash('Komentar berhasil ditambahkan.');
        $this->redirect('/tickets/' . $id);
    }

    /**
     * Memperbarui isi komentar. Hanya penulis komentar sendiri
     * yang boleh mengedit.
     *
     * @param int $commentId
     */
    public function updateComment($commentId)
    {
        $this->requireRole(['user', 'teknisi', 'admin']);

        $commentModel = new Comment();
        $comment = $commentModel->find($commentId);

        if (!$comment || $comment['user_id'] != $_SESSION['user']['id']) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        $message = trim($_POST['message'] ?? '');

        if (!Validator::lengthBetween($message, 1, 1000)) {
            $this->flash('Komentar tidak boleh kosong dan maksimal 1000 karakter.', 'error');
            $this->redirect('/tickets/' . $comment['ticket_id']);
        }

        $commentModel->update($commentId, $message);
        $this->flash('Komentar berhasil diperbarui.');
        $this->redirect('/tickets/' . $comment['ticket_id']);
    }

    /**
     * Menghapus komentar. Penulis komentar atau admin yang boleh menghapus.
     *
     * @param int $commentId
     */
    public function deleteComment($commentId)
    {
        $this->requireRole(['user', 'teknisi', 'admin']);

        $commentModel = new Comment();
        $comment = $commentModel->find($commentId);

        if (!$comment) {
            http_response_code(404);
            echo 'Komentar tidak ditemukan.';
            return;
        }

        $isOwner = $comment['user_id'] == $_SESSION['user']['id'];
        $isAdmin = $_SESSION['user']['role'] === 'admin';

        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        $commentModel->delete($commentId);
        $this->flash('Komentar berhasil dihapus.');
        $this->redirect('/tickets/' . $comment['ticket_id']);
    }

    /**
     * Menghapus lampiran, baik dari database maupun file fisiknya.
     * Hanya pemilik tiket atau admin yang boleh menghapus.
     *
     * @param int $attachmentId
     */
    public function deleteAttachment($attachmentId)
    {
        $this->requireRole(['user', 'admin']);

        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->find($attachmentId);

        if (!$attachment) {
            http_response_code(404);
            echo 'Lampiran tidak ditemukan.';
            return;
        }

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($attachment['ticket_id']);

        $isOwner = $ticket['user_id'] == $_SESSION['user']['id'];
        $isAdmin = $_SESSION['user']['role'] === 'admin';

        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        $filePath = __DIR__ . '/../../public' . $attachment['file_path'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $attachmentModel->delete($attachmentId);
        $this->flash('Lampiran berhasil dihapus.');
        $this->redirect('/tickets/' . $attachment['ticket_id']);
    }

    /**
     * Memvalidasi dan menyimpan file lampiran ke folder uploads.
     * Validasi mencakup ekstensi file dan ukuran maksimal.
     * Nama file di-uniqid-kan agar tidak bentrok dan tidak predictable.
     *
     * @param array $file Entry dari $_FILES
     * @param int $ticketId
     */
    private function handleUpload($file, $ticketId)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $this->allowedTypes, true) || $file['size'] > $this->maxSize) {
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($realMime, $this->allowedMimes, true)) {
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/';
        $fileName = uniqid('ticket_' . $ticketId . '_') . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            $attachmentModel = new Attachment();
            $attachmentModel->create($ticketId, '/uploads/' . $fileName, $ext, $file['size']);
        }
    }
}
