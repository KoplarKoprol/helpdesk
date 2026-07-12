<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Edit Kategori</h2>
    <form action="<?= url('/categories/' . $category['id'] . '/update') ?>" method="POST">
        <?= csrfField() ?>
        <p>
            <label>Nama Kategori</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
        </p>
        <p>
            <label>Deskripsi</label><br>
            <textarea name="description" rows="3"><?= htmlspecialchars($category['description']) ?></textarea>
        </p>
        <button type="submit">Simpan Perubahan</button>
    </form>
    <p><a href="<?= url('/categories') ?>">Kembali</a></p>
</body>
</html>
