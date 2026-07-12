<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Tiket</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Buat Tiket Baru</h2>
    <form action="<?= url('/tickets') ?>" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <p>
            <label>Judul</label><br>
            <input type="text" name="title" required>
        </p>
        <p>
            <label>Kategori</label><br>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>Nomor Telepon yang Bisa Dihubungi</label><br>
            <input type="tel" name="contact_phone" placeholder="Contoh: 081234567890" pattern="\+?[0-9]{9,15}" required>
        </p>
        <p>
            <label>Prioritas</label><br>
            <select name="priority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
        </p>
        <p>
            <label>Deskripsi</label><br>
            <textarea name="description" rows="5" required></textarea>
        </p>
        <p>
            <label>Lampiran (jpg, png, pdf, docx, maks 2MB)</label><br>
            <input type="file" name="attachment">
        </p>
        <button type="submit">Kirim Tiket</button>
    </form>
</body>
</html>
