<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tiket</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Daftar Tiket</h2>
    <p>
        <a href="<?= url('/logout') ?>">Logout</a>
        <?php if ($role === 'admin'): ?>
            | <a href="<?= url('/admin/dashboard') ?>">Kembali ke Dashboard</a>
        <?php endif; ?>
    </p>

    <?php if ($role === 'user'): ?>
        <p><a href="<?= url('/tickets/create') ?>">+ Buat Tiket Baru</a></p>
    <?php endif; ?>

    <form action="<?= url('/tickets') ?>" method="GET" style="margin-bottom: 12px;">
        <input type="text" name="keyword" placeholder="Cari judul tiket..." value="<?= htmlspecialchars($filters['keyword']) ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="resolved" <?= $filters['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
            <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <select name="category_id">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>" <?= $filters['category_id'] == $category['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="<?= url('/tickets') ?>">Reset</a>
    </form>

    <table border="1" cellpadding="8">
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($tickets as $ticket): ?>
        <tr <?= $ticket['priority'] === 'high' ? 'style="background-color: #f8d7da;"' : '' ?>>
            <td><?= htmlspecialchars($ticket['title']) ?></td>
            <td><?= htmlspecialchars($ticket['kategori']) ?></td>
            <td><?= htmlspecialchars($ticket['priority']) ?></td>
            <td><?= htmlspecialchars($ticket['status']) ?></td>
            <td><a href="<?= url('/tickets/' . $ticket['id']) ?>">Detail</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
        <tr><td colspan="5">Belum ada tiket.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
