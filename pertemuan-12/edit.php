<?php
session_start();
require 'koneksi.php';
require 'fungsi.php';


$cid = filter_input(INPUT_GET, 'cid', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$cid) {
    $_SESSION['flash_error'] = "Akses tidak valid";
    redirect_ke('read.php');
    exit;
}


$stmt = mysqli_prepare(
    $conn,
    "SELECT cid, cnama, cemail, cpesan
     FROM tbl_tamu
     WHERE cid = ?
     LIMIT 1"
);

if (!$stmt) {
    $_SESSION['flash_error'] = "Query tidak benar";
    redirect_ke('read.php');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $cid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data   = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['flash_error'] = "Data tidak ditemukan";
    redirect_ke('read.php');
    exit;
}

/* data dari DB */
$nama  = $data['cnama'];
$email = $data['cemail'];
$pesan = $data['cpesan'];

/* flash & old */
$flash_error = $_SESSION['flash_error'] ?? '';
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['old']);

/* jika redirect karena error */
if (!empty($old)) {
    $nama    = $old['nama']    ?? $nama;
    $email   = $old['email']   ?? $email;
    $pesan   = $old['pesan']   ?? $pesan;
    $captcha = $old['captcha'] ?? '';
} else {
    $captcha = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku Tamu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Ini Header</h1>
    <nav>
        <ul>
            <li><a href="index.php">Beranda</a></li>
            <li><a href="#about">Tentang</a></li>
            <li><a href="#contact">Kontak</a></li>
        </ul>
    </nav>
</header>

<main id="contact">
    <h2>Edit Buku Tamu</h2>

    <?php if (!empty($flash_error)) : ?>
        <div style="padding:10px;background:#f8d7da;color:#721c24;border-radius:6px;margin-bottom:10px;">
            <?= $flash_error ?>
        </div>
    <?php endif; ?>

    <form action="proses_update.php" method="POST">

        <input type="txt" name="cid" value="<?= (int)$cid ?>">

        <label>
            <span>Nama:</span>
            <input type="text" name="txtNamaEd" required
                   value="<?= htmlspecialchars($nama) ?>">
        </label>

        <label>
            <span>Email:</span>
            <input type="email" name="txtEmailEd" required
                   value="<?= htmlspecialchars($email) ?>">
        </label>

        <label>
            <span>Pesan Anda:</span>
            <textarea name="txtPesanEd" rows="4" required><?= htmlspecialchars($pesan) ?></textarea>
        </label>

        <label>
            <span>Captcha 1 x 1 = ?</span>
            <input type="number" name="txtCaptcha" required
                   value="<?= htmlspecialchars($captcha) ?>">
        </label>

        <button type="submit">Kirim</button>
        <button type="reset">Batal</button>
        <a href="read.php" class="reset">Kembali</a>

    </form>
</main>

</body>
</html>