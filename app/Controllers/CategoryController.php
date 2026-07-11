<?php

/**
 * CategoryController.
 *
 * CRUD kategori tiket (Hardware, Software, dll). Semua method
 * dibatasi hanya untuk role admin.
 */
class CategoryController extends Controller
{
    public function index()
    {
        $this->requireRole(['admin']);

        $categoryModel = new Category();
        $this->view('categories/index', ['categories' => $categoryModel->all()]);
    }

    public function create()
    {
        $this->requireRole(['admin']);
        $this->view('categories/create');
    }

    public function store()
    {
        $this->requireRole(['admin']);

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!Validator::lengthBetween($name, 2, 50)) {
            $this->flash('Nama kategori wajib diisi, 2-50 karakter.', 'error');
            $this->redirect('/categories/create');
        }

        if (mb_strlen($description) > 255) {
            $this->flash('Deskripsi maksimal 255 karakter.', 'error');
            $this->redirect('/categories/create');
        }

        $categoryModel = new Category();
        $categoryModel->create($name, $description);

        $this->flash('Kategori berhasil ditambahkan.');
        $this->redirect('/categories');
    }

    public function edit($id)
    {
        $this->requireRole(['admin']);

        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            http_response_code(404);
            echo 'Kategori tidak ditemukan.';
            return;
        }

        $this->view('categories/edit', ['category' => $category]);
    }

    public function update($id)
    {
        $this->requireRole(['admin']);

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!Validator::lengthBetween($name, 2, 50)) {
            $this->flash('Nama kategori wajib diisi, 2-50 karakter.', 'error');
            $this->redirect('/categories/' . $id . '/edit');
        }

        if (mb_strlen($description) > 255) {
            $this->flash('Deskripsi maksimal 255 karakter.', 'error');
            $this->redirect('/categories/' . $id . '/edit');
        }

        $categoryModel = new Category();
        $categoryModel->update($id, $name, $description);

        $this->flash('Kategori berhasil diperbarui.');
        $this->redirect('/categories');
    }

    /**
     * Menghapus kategori. Ditolak kalau masih ada tiket yang memakainya,
     * supaya data tiket lama tidak kehilangan kategori (integritas data).
     */
    public function destroy($id)
    {
        $this->requireRole(['admin']);

        $categoryModel = new Category();

        if ($categoryModel->countTickets($id) > 0) {
            $this->flash('Kategori tidak bisa dihapus karena masih dipakai oleh tiket.', 'error');
            $this->redirect('/categories');
        }

        $categoryModel->delete($id);
        $this->flash('Kategori berhasil dihapus.');
        $this->redirect('/categories');
    }
}
