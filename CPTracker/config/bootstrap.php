<?php
/**
 * Helper bersama untuk semua halaman aplikasi.
 * File ini menyediakan session, escaping HTML, validasi input, CSRF,
 * redirect setelah POST, serta kerangka header dan footer halaman.
 */

// Session menyimpan token CSRF yang dipakai untuk memverifikasi formulir.
// Pemeriksaan mencegah session_start() dipanggil berulang kali dalam request.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Escape nilai sebelum disisipkan ke dalam HTML.
 * ENT_QUOTES menangani kutip tunggal maupun ganda, ENT_SUBSTITUTE mengganti
 * byte UTF-8 yang tidak valid, dan UTF-8 digunakan sebagai charset output.
 * Jangan gunakan fungsi ini untuk menyiapkan nilai SQL; PDO menangani itu.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Ambil token CSRF sesi saat ini atau buat token acak baru.
 * Token disimpan dalam session supaya dapat dibandingkan saat form dikirim.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        // Token acak kriptografis membuat nilai sulit ditebak dari browser lain.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Pastikan request POST membawa token CSRF yang sama dengan token session.
 * Jika tidak cocok, hentikan request dengan status HTTP 403 sebelum perubahan data.
 */
function verify_csrf(): void
{
    // Ambil field form; nilai yang tidak ada akan dianggap string kosong.
    $sent = $_POST['csrf_token'] ?? '';
    // hash_equals membandingkan token dengan cara yang aman terhadap timing attack.
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }
}

/**
 * Kirim header redirect lalu hentikan script.
 * Dipakai setelah POST agar refresh browser tidak mengirim ulang form (PRG).
 */
function redirect_to(string $location): void
{
    header('Location: ' . $location);
    exit;
}

/**
 * Ambil teks dari POST, hilangkan spasi di tepi, dan validasi panjangnya.
 * Field kosong atau lebih panjang dari batas akan menghasilkan exception
 * yang ditangkap halaman pemanggil untuk ditampilkan kepada pengguna.
 */
function post_string(string $key, int $maxLength = 255): string
{
    // Cast menjamin input berupa string sebelum diproses.
    $value = trim((string) ($_POST[$key] ?? ''));
    // Hitung karakter Unicode, bukan byte, agar batas konsisten dengan form HTML.
    $characterCount = preg_match_all('/./us', $value);
    if ($value === '' || $characterCount === false || $characterCount > $maxLength) {
        throw new InvalidArgumentException("{$key} wajib diisi (maksimal {$maxLength} karakter).");
    }
    return $value;
}

/**
 * Ambil ID dari POST dan pastikan nilainya integer positif.
 * Hasilnya aman diteruskan ke method model yang menerima parameter int.
 */
function post_id(string $key): int
{
    // FILTER_VALIDATE_INT menolak teks, pecahan, dan angka di bawah satu.
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($value === false) {
        throw new InvalidArgumentException('ID tidak valid.');
    }
    return $value;
}

/**
 * Render awal dokumen HTML, tautan stylesheet, brand, dan navigasi utama.
 * Parameter title dipakai untuk judul tab browser.
 */
function page_header(string $title): void
{
    // Halaman dalam /view/ butuh ../ untuk mencapai root proyek;
    // dashboard berada di root sehingga tidak membutuhkan awalan tersebut.
    $base = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/view/') ? '../' : '';
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · CP Tracker</title>
        <!-- Pakai stylesheet bersama dan escape path sebelum dirender. -->
        <link rel="stylesheet" href="<?= e($base) ?>assets/style.css">
    </head>
    <body>
    <header class="topbar">
        <a class="brand" href="<?= e($base) ?>index.php">CP Tracker<span> / practice archive</span></a>
        <!-- Semua halaman memakai navigasi yang sama agar perpindahan konsisten. -->
        <nav><a href="<?= e($base) ?>index.php">Dashboard</a><a href="<?= e($base) ?>view/category.php">Kategori</a><a href="<?= e($base) ?>view/problem.php">Soal</a><a href="<?= e($base) ?>view/submission.php">Submission</a></nav>
    </header>
    <main class="container">
    <?php
}

/**
 * Tutup elemen utama HTML dan tampilkan footer bersama.
 * Dipanggil setelah konten spesifik halaman selesai dirender.
 */
function page_footer(): void
{
    ?>
    </main><footer class="footer">CP Tracker · Arsip latihan Competitive Programming</footer>
    </body></html>
    <?php
}

/**
 * Cetak input tersembunyi berisi token CSRF untuk disertakan pada form POST.
 * Token di-escape sebagai output HTML sebelum dimasukkan ke atribut value.
 */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
