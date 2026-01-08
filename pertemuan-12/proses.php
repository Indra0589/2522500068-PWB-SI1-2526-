<?php
session_start();

require 'fungsi.php';
require 'koneksi.php';

// hanya boleh POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = 'Akses tidak valid.';
    redirect_ke('index.php#contact');
    exit;
}

// ambil input
$nama    = bersihkan($_POST['txtNama'] ?? '');
$email   = bersihkan($_POST['txtEmail'] ?? '');
$pesan   = bersihkan($_POST['txtPesan'] ?? '');
$captcha = bersihkan($_POST['txtcaptcha'] ?? '');

// validasi
$errors = [];

if ($nama === '') {
    $errors[] = 'Nama wajib diisi.';
}

if ($email === '') {
    $errors[] = 'Email wajib diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}

if ($pesan === '') {
    $errors[] = 'Pesan wajib diisi.';
}

if ($captcha !== '') {
    $errors[] = 'Captcha salah.';
}


// kalau ada error → kembali
if (!empty($errors)) {
    $_SESSION['old'] = [
        'nama'     => $nama,
        'email'    => $email,
        'pesan'    => $pesan,
        'captcha'  => $captcha
    ];
    $_SESSION['flash_error'] = implode('<br>', $errors);
    redirect_ke('index.php#contact');
    exit;
}

// INSERT data (prepared statement)
$sql = "INSERT INTO tbl_tamu (cnama, cemail, cpesan) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['flash_error'] = 'Prepare gagal: ' . mysqli_error($conn);
    redirect_ke('index.php#contact');
    exit;
}

mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $pesan);

if (mysqli_stmt_execute($stmt)) {
    unset($_SESSION['old']);
    unset($_SESSION['captcha']);
    $_SESSION['flash_sukses'] = 'Terima kasih, data Anda berhasil disimpan.';
} else {
    $_SESSION['flash_error'] = 'Gagal menyimpan data: ' . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
redirect_ke('index.php#contact');
exit;
