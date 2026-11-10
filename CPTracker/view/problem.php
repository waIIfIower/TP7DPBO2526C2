<?php
// Controller halaman soal: memproses CRUD dan menyiapkan kategori untuk dropdown.
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../class/Problem.php';
require_once __DIR__ . '/../class/Category.php';

$problemObj = new Problem();
$categoryObj = new Category();
$error = '';
$editing = null;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi token dan field dilakukan sebelum model mengubah database.
        verify_csrf();
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $problemObj->delete(post_id('id'));
        } elseif ($action === 'save') {
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? post_id('id') : null;
            $categoryId = post_id('category_id');
            $title = post_string('title', 180);
            $platform = post_string('platform', 80);
            $difficulty = post_string('difficulty', 40);
            // Judul soal boleh sama di platform berbeda, tetapi tidak boleh
            // tercatat dua kali pada platform yang sama.
            if ($problemObj->duplicateExists($title, $platform, $id)) {
                throw new InvalidArgumentException('Soal dengan judul dan platform tersebut sudah tercatat.');
            }
            if ($id === null) $problemObj->create($categoryId, $title, $platform, $difficulty);
            else $problemObj->update($id, $categoryId, $title, $platform, $difficulty);
        }
        redirect_to('problem.php');
    }
    if (isset($_GET['edit'])) $editing = $problemObj->readById((int) $_GET['edit']);
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
}
$categories = $categoryObj->readAll()->fetchAll();
page_header('Soal');
?>
<!-- Form soal memilih kategori yang sudah ada; tabel menampilkan hasil JOIN. -->
<div class="page-heading"><div><p class="eyebrow">PROBLEM SET</p><h1>Daftar soal</h1><p>Simpan soal dari berbagai platform dan hubungkan dengan ladder.</p></div></div>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (!$categories): ?><div class="alert">Tambahkan kategori terlebih dahulu sebelum mencatat soal.</div><?php endif; ?>
<section class="panel"><h2><?= $editing ? 'Edit soal' : 'Tambah soal' ?></h2>
<form method="post" class="form-grid"><?php csrf_field(); ?><input type="hidden" name="action" value="save"><?php if ($editing): ?><input type="hidden" name="id" value="<?= e($editing['id']) ?>"><?php endif; ?>
<label>Kategori<select name="category_id" required><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= e($category['id']) ?>" <?= (string) ($editing['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
<label>Platform<input name="platform" maxlength="80" required value="<?= e($editing['platform'] ?? '') ?>" placeholder="Codeforces, TLX, AtCoder..."></label>
<label>Judul soal<input name="title" maxlength="180" required value="<?= e($editing['title'] ?? '') ?>" placeholder="Contoh: Watermelon"></label>
<label>Kesulitan<input name="difficulty" maxlength="40" required value="<?= e($editing['difficulty'] ?? '') ?>" placeholder="800, Easy, Medium..."></label>
<div class="form-actions"><button class="button" type="submit" <?= !$categories ? 'disabled' : '' ?>><?= $editing ? 'Simpan perubahan' : 'Tambah soal' ?></button><?php if ($editing): ?><a class="button secondary" href="problem.php">Batal</a><?php endif; ?></div></form></section>
<section class="panel"><div class="section-title"><h2>Semua soal</h2><span><?= $problemObj->countAll() ?> soal</span></div><div class="table-wrap"><table><thead><tr><th>Judul</th><th>Kategori</th><th>Platform</th><th>Kesulitan</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($problemObj->readAll() as $row): ?><tr><td class="strong"><?= e($row['title']) ?></td><td><?= e($row['category_name']) ?></td><td><?= e($row['platform']) ?></td><td><span class="badge"><?= e($row['difficulty']) ?></span></td><td class="actions"><a class="text-link" href="?edit=<?= e($row['id']) ?>">Edit</a><form method="post" onsubmit="return confirm('Hapus soal ini beserta seluruh riwayat submission-nya?');"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="text-button danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></section><?php page_footer(); ?>
