<?php
// Dashboard utama: siapkan model, ambil statistik, lalu render ringkasan aplikasi.
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/class/Category.php';
require_once __DIR__ . '/class/Problem.php';
require_once __DIR__ . '/class/Submission.php';

$categoryObj = new Category();
$problemObj = new Problem();
$submissionObj = new Submission();
page_header('Dashboard');
?>
<section class="hero"><p class="eyebrow">YOUR ALGORITHM PRACTICE, ORGANIZED</p><h1>Latihan terarah. Progress tercatat.</h1><p>Arsip soal Competitive Programming berdasarkan ladder, platform, tingkat kesulitan, dan hasil percobaan.</p><a class="button" href="view/problem.php">Lihat daftar soal →</a></section>
<section class="stats">
    <article class="stat"><div class="stat-label">Kategori / ladder</div><div class="stat-number"><?= $categoryObj->countAll() ?></div><a href="view/category.php">Kelola kategori →</a></article>
    <article class="stat"><div class="stat-label">Soal tersimpan</div><div class="stat-number"><?= $problemObj->countAll() ?></div><a href="view/problem.php">Kelola soal →</a></article>
    <article class="stat"><div class="stat-label">Riwayat submission</div><div class="stat-number"><?= $submissionObj->countAll() ?></div><a href="view/submission.php">Lihat percobaan →</a></article>
</section>
<section class="panel"><h2>Alur penggunaan</h2><p>Mulai dengan membuat kategori latihan, tambahkan soal ke kategori tersebut, lalu catat setiap percobaan beserta bahasa dan verdict-nya. Relasi database menjaga soal dan submission tetap terhubung.</p></section>
<?php page_footer(); ?>
