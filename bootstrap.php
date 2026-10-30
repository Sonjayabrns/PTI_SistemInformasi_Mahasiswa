<?php

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$translations = [
    'id' => [
        'app_title' => 'Sistem Informasi Data Mahasiswa',
        'login' => 'Masuk',
        'logout' => 'Keluar',
        'username' => 'Nama pengguna',
        'password' => 'Kata sandi',
        'login_title' => 'Login Administrator',
        'login_failed' => 'Nama pengguna atau kata sandi salah.',
        'login_required' => 'Silakan masuk untuk melanjutkan.',
        'dashboard' => 'Data Mahasiswa',
        'add_student' => 'Tambah Mahasiswa',
        'edit_student' => 'Ubah Data Mahasiswa',
        'nim' => 'NIM',
        'name' => 'Nama lengkap',
        'email' => 'Email',
        'program' => 'Program studi',
        'year' => 'Angkatan',
        'actions' => 'Aksi',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
        'confirm_delete' => 'Yakin ingin menghapus data mahasiswa ini?',
        'no_students' => 'Belum ada data mahasiswa.',
        'required_fields' => 'Semua kolom wajib diisi.',
        'invalid_email' => 'Format email tidak valid.',
        'invalid_year' => 'Angkatan harus berupa tahun yang valid.',
        'student_saved' => 'Data mahasiswa berhasil disimpan.',
        'student_deleted' => 'Data mahasiswa berhasil dihapus.',
        'duplicate_data' => 'NIM atau email tersebut sudah digunakan.',
        'save_failed' => 'Data tidak dapat disimpan. Silakan coba lagi.',
        'delete_failed' => 'Data tidak dapat dihapus. Silakan coba lagi.',
        'invalid_request' => 'Permintaan tidak valid.',
        'db_error' => 'Database tidak dapat diakses. Periksa konfigurasi dan jalankan database.sql.',
        'language' => 'Bahasa',
        'indonesian' => 'Indonesia',
        'english' => 'Inggris',
        'welcome' => 'Selamat datang',
        'no_access' => 'Anda tidak memiliki akses.',
    ],
    'en' => [
        'app_title' => 'Student Information Management System',
        'login' => 'Sign in',
        'logout' => 'Sign out',
        'username' => 'Username',
        'password' => 'Password',
        'login_title' => 'Administrator Login',
        'login_failed' => 'The username or password is incorrect.',
        'login_required' => 'Please sign in to continue.',
        'dashboard' => 'Student Records',
        'add_student' => 'Add Student',
        'edit_student' => 'Edit Student',
        'nim' => 'Student ID',
        'name' => 'Full name',
        'email' => 'Email',
        'program' => 'Program',
        'year' => 'Enrollment year',
        'actions' => 'Actions',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'confirm_delete' => 'Are you sure you want to delete this student?',
        'no_students' => 'There are no student records yet.',
        'required_fields' => 'All fields are required.',
        'invalid_email' => 'The email address is invalid.',
        'invalid_year' => 'Enter a valid enrollment year.',
        'student_saved' => 'Student record saved successfully.',
        'student_deleted' => 'Student record deleted successfully.',
        'duplicate_data' => 'That student ID or email is already in use.',
        'save_failed' => 'The record could not be saved. Please try again.',
        'delete_failed' => 'The record could not be deleted. Please try again.',
        'invalid_request' => 'The request is invalid.',
        'db_error' => 'The database is unavailable. Check the configuration and run database.sql.',
        'language' => 'Language',
        'indonesian' => 'Indonesian',
        'english' => 'English',
        'welcome' => 'Welcome',
        'no_access' => 'You do not have access.',
    ],
];

if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'], true)) {
    $_SESSION['language'] = $_GET['lang'];
}

function t(string $key): string
{
    global $translations;
    $language = $_SESSION['language'] ?? 'id';
    return $translations[$language][$key] ?? $key;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => t('login_required')];
        header('Location: login.php');
        exit;
    }
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}
