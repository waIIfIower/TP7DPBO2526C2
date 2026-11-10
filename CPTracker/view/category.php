<?php
// Controller halaman kategori: validasi POST, jalankan CRUD, lalu render data.
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../class/Category.php';

$categoryObj = new Category();
$error = '';
$editing = null;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Semua perubahan harus berasal dari form bersesi dengan token CSRF valid.
        verify_csrf();
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $categoryObj->delete(post_id('id'));
        } elseif ($action === 'save') {
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? post_id('id') : null;
            $name = post_string('name', 100);
            $description = trim((string) ($_POST['description'] ?? ''));
            $descriptionLength = preg_match_all('/./us', $description);
            if ($descriptionLength === false || $descriptionLength > 1000) {
                throw new InvalidArgumentException('Deskripsi maksimal 1000 karakter.');
            }
            if ($id === null) $categoryObj->create($name, $description);
            else $categoryObj->update($id, $name, $description);
        }
        redirect_to('category.php');
    }
    if (isset($_GET['edit'])) {
        $editing = $categoryObj->readById((int) $_GET['edit']);
    }
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
}

page_header('Kategori');
?>
<!-- Form tambah/edit dan tabel daftar kategori. -->
<div class="page-heading"><div><p class="eyebrow">MASTER DATA</p><h1>Kategori latihan</h1><p>Kelompokkan soal ke dalam ladder atau topik latihan.</p></div></div>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<section class="panel">
    <h2><?= $editing ? 'Edit kategori' : 'Tambah kategori' ?></h2>
    <form method="post" class="form-grid">
        <?php csrf_field(); ?><input type="hidden" name="action" value="save">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($editing['id']) ?>"><?php endif; ?>
        <label>Nama kategori<input name="name" maxlength="100" required value="<?= e($editing['name'] ?? '') ?>" placeholder="Contoh: Codeforces Div. 2"></label>
        <label>Deskripsi<textarea name="description" maxlength="1000" rows="3" placeholder="Fokus materi atau target latihan"><?= e($editing['description'] ?? '') ?></textarea></label>
        <div class="form-actions"><button class="button" type="submit"><?= $editing ? 'Simpan perubahan' : 'Tambah kategori' ?></button><?php if ($editing): ?><a class="button secondary" href="category.php">Batal</a><?php endif; ?></div>
    </form>
</section>
<section class="panel"><div class="section-title"><h2>Daftar kategori</h2><span><?= $categoryObj->countAll() ?> kategori</span></div>
<div class="table-wrap"><table><thead><tr><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($categoryObj->readAll() as $row): ?><tr><td class="strong"><?= e($row['name']) ?></td><td><?= e($row['description'] ?: '—') ?></td><td class="actions"><a class="text-link" href="?edit=<?= e($row['id']) ?>">Edit</a><form method="post" onsubmit="return confirm('Hapus kategori ini? Semua soal dan submission turunannya akan ikut terhapus.');"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="text-button danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php page_footer(); ?>
