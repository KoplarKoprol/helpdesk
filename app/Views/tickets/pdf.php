<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #999; padding: 6px; text-align: left; }
        .label { width: 150px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Detail Tiket #<?= $ticket['id'] ?></h2>
    <p>Dicetak pada: <?= date('d/m/Y H:i') ?></p>

    <table>
        <tr><td class="label">Judul</td><td><?= htmlspecialchars($ticket['title']) ?></td></tr>
        <tr><td class="label">Pembuat</td><td><?= htmlspecialchars($ticket['pembuat']) ?></td></tr>
        <tr><td class="label">Kategori</td><td><?= htmlspecialchars($ticket['kategori']) ?></td></tr>
        <tr><td class="label">Prioritas</td><td><?= htmlspecialchars($ticket['priority']) ?></td></tr>
        <tr><td class="label">Kontak</td><td><?= htmlspecialchars($ticket['contact_phone']) ?></td></tr>
        <tr><td class="label">Status</td><td><?= htmlspecialchars($ticket['status']) ?></td></tr>
        <tr><td class="label">Teknisi</td><td><?= htmlspecialchars($ticket['teknisi'] ?? '-') ?></td></tr>
        <tr><td class="label">Tanggal Dibuat</td><td><?= htmlspecialchars($ticket['created_at']) ?></td></tr>
    </table>

    <h3>Deskripsi</h3>
    <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

    <h3>Riwayat Komentar</h3>
    <table>
        <tr><th>Penulis</th><th>Komentar</th><th>Waktu</th></tr>
        <?php foreach ($comments as $comment): ?>
        <tr>
            <td><?= htmlspecialchars($comment['penulis']) ?></td>
            <td><?= htmlspecialchars($comment['message']) ?></td>
            <td><?= htmlspecialchars($comment['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($comments)): ?>
        <tr><td colspan="3">Belum ada komentar.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
