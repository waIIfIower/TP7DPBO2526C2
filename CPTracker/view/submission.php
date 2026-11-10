<?php
// Controller halaman submission: validasi input, proses CRUD, siapkan dropdown soal.
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../class/Submission.php';
require_once __DIR__ . '/../class/Problem.php';

$submissionObj = new Submission();
$problemObj = new Problem();
$error = '';
$editing = null;
$languages = ['C++', 'C', 'Java', 'Python', 'Pascal', 'JavaScript', 'Lainnya'];
$verdicts = ['Accepted (AC)', 'Wrong Answer (WA)', 'Time Limit Exceeded (TLE)', 'Memory Limit Exceeded (MLE)', 'Runtime Error (RTE)', 'Compilation Error (CE)'];
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verifikasi CSRF dan pastikan bahasa/verdict termasuk pilihan yang diizinkan.
        verify_csrf();
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $submissionObj->delete(post_id('id'));
        } elseif ($action === 'save') {
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? post_id('id') : null;
            $problemId = post_id('problem_id');
            $language = post_string('language', 40);
            $verdict = post_string('verdict', 60);
            if (!in_array($language, $languages, true) || !in_array($verdict, $verdicts, true)) {
                throw new InvalidArgumentException('Bahasa atau verdict tidak dikenal.');
            }
            if ($id === null) $submissionObj->create($problemId, $language, $verdict);
            else $submissionObj->update($id, $problemId, $language, $verdict);
        }
        redirect_to('submission.php');
    }
    if (isset($_GET['edit'])) $editing = $submissionObj->readById((int) $_GET['edit']);
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
}
$problems = $problemObj->readAll()->fetchAll();
page_header('Submission');
?>
<!-- Form mencatat percobaan; tabel di bawahnya merangkum riwayat dan verdict. -->
<div class="page-heading"><div><p class="eyebrow">ATTEMPT LOG</p><h1>Riwayat submission</h1><p>Catat bahasa dan hasil untuk setiap percobaan menyelesaikan soal.</p></div></div>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (!$problems): ?><div class="alert">Tambahkan soal terlebih dahulu sebelum mencatat submission.</div><?php endif; ?>
<section class="panel"><h2><?= $editing ? 'Edit submission' : 'Catat submission' ?></h2><form method="post" class="form-grid">
<?php csrf_field(); ?><input type="hidden" name="action" value="save"><?php if ($editing): ?><input type="hidden" name="id" value="<?= e($editing['id']) ?>"><?php endif; ?>
<label>Soal<select name="problem_id" required><option value="">Pilih soal</option><?php foreach ($problems as $problem): ?><option value="<?= e($problem['id']) ?>" <?= (string) ($editing['problem_id'] ?? '') === (string) $problem['id'] ? 'selected' : '' ?>><?= e($problem['title']) ?> · <?= e($problem['platform']) ?></option><?php endforeach; ?></select></label>
<label>Bahasa<select name="language" required><?php foreach ($languages as $language): ?><option value="<?= e($language) ?>" <?= ($editing['language'] ?? 'C++') === $language ? 'selected' : '' ?>><?= e($language) ?></option><?php endforeach; ?></select></label>
<label>Verdict<select name="verdict" required><?php foreach ($verdicts as $verdict): ?><option value="<?= e($verdict) ?>" <?= ($editing['verdict'] ?? '') === $verdict ? 'selected' : '' ?>><?= e($verdict) ?></option><?php endforeach; ?></select></label>
<div class="form-actions"><button class="button" type="submit" <?= !$problems ? 'disabled' : '' ?>><?= $editing ? 'Simpan perubahan' : 'Tambah submission' ?></button><?php if ($editing): ?><a class="button secondary" href="submission.php">Batal</a><?php endif; ?></div></form></section>
<section class="panel"><div class="section-title"><h2>Semua percobaan</h2><span><?= $submissionObj->countAll() ?> submission</span></div><div class="table-wrap"><table><thead><tr><th>Soal</th><th>Bahasa</th><th>Verdict</th><th>Waktu</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($submissionObj->readAll() as $row): $verdictClass = str_contains($row['verdict'], 'Wrong Answer') ? 'bad' : (str_contains($row['verdict'], 'Time Limit') ? 'warn' : (str_contains($row['verdict'], 'Accepted') ? '' : 'warn')); ?><tr><td class="strong"><?= e($row['problem_title'] ?? 'Soal dihapus') ?></td><td><?= e($row['language']) ?></td><td><span class="badge <?= e($verdictClass) ?>"><?= e($row['verdict']) ?></span></td><td><?= e($row['submitted_at']) ?></td><td class="actions"><a class="text-link" href="?edit=<?= e($row['id']) ?>">Edit</a><form method="post" onsubmit="return confirm('Hapus catatan submission ini?');"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="text-button danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></section><?php page_footer(); ?>
