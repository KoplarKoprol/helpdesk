<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kategori</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Manajemen Kategori</h2>
    <p>
        <a href="<?= url('/categories/create') ?>">+ Tambah Kategori</a> |
        <a href="<?= url('/admin/dashboard') ?>">Kembali ke Dashboard</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($categories as $category): ?>
        <tr>
            <td><?= htmlspecialchars($category['name']) ?></td>
            <td><?= htmlspecialchars($category['description']) ?></td>
            <td>
                <a href="<?= url('/categories/' . $category['id'] . '/edit') ?>">Edit</a>
                <form action="<?= url('/categories/' . $category['id'] . '/delete') ?>" method="POST" style="display:inline" onsubmit="return confirm('Hapus kategori ini?')">
        <?= csrfField() ?>
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr><td colspan="3">Belum ada kategori.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
