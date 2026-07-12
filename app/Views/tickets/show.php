<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Tiket</title>
</head>

<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2><?= htmlspecialchars($ticket['title']) ?></h2>
    <p>Pembuat: <?= htmlspecialchars($ticket['pembuat']) ?></p>
    <p>Kategori: <?= htmlspecialchars($ticket['kategori']) ?></p>
    <p>Prioritas: <?= htmlspecialchars($ticket['priority']) ?></p>
    <p>Kontak: <?= htmlspecialchars($ticket['contact_phone']) ?></p>
    <p>Status: <?= htmlspecialchars($ticket['status']) ?></p>
    <p>Teknisi: <?= htmlspecialchars($ticket['teknisi'] ?? '-') ?></p>
    <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

    <?php if ($_SESSION['user']['role'] === 'user' && $ticket['user_id'] == $_SESSION['user']['id']): ?>
        <p><a href="<?= url('/tickets/' . $ticket['id'] . '/edit') ?>">Edit Tiket</a></p>
    <?php endif; ?>

    <h3>Lampiran</h3>
    <ul>
        <?php foreach ($attachments as $file): ?>
            <li>
                <a href="<?= url(htmlspecialchars($file['file_path'])) ?>"
                    target="_blank"><?= htmlspecialchars(basename($file['file_path'])) ?></a>
                <?php if ($ticket['user_id'] == $_SESSION['user']['id'] || $_SESSION['user']['role'] === 'admin'): ?>
                    <form action="<?= url('/attachments/' . $file['id'] . '/delete') ?>" method="POST" style="display:inline"
                        onsubmit="return confirm('Hapus lampiran ini?')">
                        <?= csrfField() ?>
                        <button type="submit">Hapus</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($attachments)): ?>
            <li>Tidak ada lampiran.</li>
        <?php endif; ?>
    </ul>

    <?php if (in_array($_SESSION['user']['role'], ['teknisi', 'admin'])): ?>
        <h3>Ubah Status</h3>
        <form action="<?= url('/tickets/' . $ticket['id'] . '/status') ?>" method="POST">
            <?= csrfField() ?>
            <select name="status">
                <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
            <button type="submit">Update Status</button>
        </form>
    <?php endif; ?>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <h3>Assign Teknisi</h3>
        <form action="<?= url('/tickets/' . $ticket['id'] . '/assign') ?>" method="POST">
            <?= csrfField() ?>
            <select name="teknisi_id" required>
                <option value="">-- Pilih Teknisi --</option>
                <?php foreach ($teknisiList as $teknisi): ?>
                    <option value="<?= $teknisi['id'] ?>" <?= $ticket['teknisi_id'] == $teknisi['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($teknisi['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Assign</button>
        </form>
        <?php if (empty($teknisiList)): ?>
            <p><em>Belum ada user dengan role teknisi. Ubah role user di dashboard admin terlebih dahulu.</em></p>
        <?php endif; ?>
    <?php endif; ?>

    <h3>Komentar / Riwayat</h3>
    <ul>
        <?php foreach ($comments as $comment): ?>
            <li>
                <strong><?= htmlspecialchars($comment['penulis']) ?>:</strong> <?= htmlspecialchars($comment['message']) ?>
                <?php if ($comment['user_id'] == $_SESSION['user']['id']): ?>
                    <details style="display:inline">
                        <summary>Edit</summary>
                        <form action="<?= url('/comments/' . $comment['id'] . '/update') ?>" method="POST">
                            <?= csrfField() ?>
                            <textarea name="message" rows="2"
                                required><?= htmlspecialchars($comment['message']) ?></textarea><br>
                            <button type="submit">Simpan</button>
                        </form>
                    </details>
                <?php endif; ?>
                <?php if ($comment['user_id'] == $_SESSION['user']['id'] || $_SESSION['user']['role'] === 'admin'): ?>
                    <form action="<?= url('/comments/' . $comment['id'] . '/delete') ?>" method="POST" style="display:inline"
                        onsubmit="return confirm('Hapus komentar ini?')">
                        <?= csrfField() ?>
                        <button type="submit">Hapus</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($comments)): ?>
            <li>Belum ada komentar.</li>
        <?php endif; ?>
    </ul>

    <form action="<?= url('/tickets/' . $ticket['id'] . '/comment') ?>" method="POST">
        <?= csrfField() ?>
        <textarea name="message" rows="3" placeholder="Tulis komentar..." required></textarea><br>
        <button type="submit">Kirim Komentar</button>
    </form>

    <?php $backUrl = $_SESSION['user']['role'] === 'admin' ? '/admin/dashboard' : '/tickets'; ?>
    <p><a href="<?= url($backUrl) ?>">Kembali</a> | <a
            href="<?= url('/tickets/' . $ticket['id'] . '/export-pdf') ?>">Cetak PDF</a></p>
</body>

</html>