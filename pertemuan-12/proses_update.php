<?php
session_start();

require __DIR__ . '/koneksi.php';
require_once __DIR__ . '/fungsi.php';

/* hanya POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = 'Akses tidak valid.';
    redirect_ke('read.php');
    exit;
}

/* validasi CID */
$cid = filter_input(INPUT_POST, 'cid', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$cid) {
    $_SESSION['flash_error'] = 'CID tidak valid.';
    redirect_ke('read.php');
    exit;
}

/* ambil input */
$nama    = bersihkan($_POST['txtNamaEd'] ?? '');
$email   = bersihkan($_POST['txtEmailEd'] ?? '');
$pesan   = bersihkan($_POST['txtPesanEd'] ?? '');
$captcha = bersihkan($_POST['txtCaptcha'] ?? '');

$errors = [];

/* validasi */
if ($nama === '') {
    $errors[] = 'Nama wajib diisi.';
} elseif (mb_strlen($nama) < 3) {
    $errors[] = 'Nama minimal 3 karakter.';
}

if ($email === '') {
    $errors[] = 'Email wajib diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format e-mail tidak valid.';
}

if ($pesan === '') {
    $errors[] = 'Pesan wajib diisi.';
} elseif (mb_strlen($pesan) < 10) {
    $errors[] = 'Pesan minimal 10 karakter.';
}

if ($captcha === '') {
    $errors[] = 'Captcha wajib diisi.';
} elseif ($captcha !== '1') {
    $errors[] = 'Jawaban captcha salah.';
}

/* jika ada error */
if (!empty($errors)) {
    $_SESSION['old'] = [
        'nama'    => $nama,
        'email'   => $email,
        'pesan'   => $pesan,
        'captcha' => $captcha,
    ];

    $_SESSION['flash_error'] = implode('<br>', $errors);
    redirect_ke('edit.php?cid=' . $cid);
    exit;
}

/* update data */
$sql = "
    UPDATE tbl_tamu
    SET cnama = ?, cemail = ?, cpesan = ?
    WHERE cid = ?
";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    $_SESSION['flash_error'] = 'Prepare statement gagal.';
    redirect_ke('edit.php?cid=' . $cid);
    exit;
}

mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $pesan, $cid);

if (mysqli_stmt_execute($stmt)) {
    unset($_SESSION['old']);
    $_SESSION['flash_sukses'] = 'Data berhasil diperbarui.';
    redirect_ke('read.php');
    exit;
}

/* jika gagal execute */
$_SESSION['flash_error'] = 'Data gagal diperbarui.';
redirect_ke('edit.php?cid=' . $cid);
exit;
