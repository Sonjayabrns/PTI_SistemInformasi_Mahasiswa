<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

function fail(string $message, string $type = 'error'): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    http_response_code(400);
    exit(t('invalid_request'));
}

$action = (string) ($_POST['action'] ?? '');
$rawId = (string) ($_POST['id'] ?? '');
$id = $rawId === ''
    ? null
    : filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($action === 'delete') {
    if (!$id) {
        fail(t('invalid_request'));
    }

    try {
        $statement = database()->prepare('DELETE FROM mahasiswa WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            fail(t('invalid_request'));
        }
        fail(t('student_deleted'), 'success');
    } catch (PDOException $exception) {
        error_log('Student delete database error: ' . $exception->getMessage());
        fail(t('delete_failed'));
    }
}

if ($action !== 'save') {
    fail(t('invalid_request'));
}

$nim = trim((string) ($_POST['nim'] ?? ''));
$name = trim((string) ($_POST['nama'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$program = trim((string) ($_POST['program_studi'] ?? ''));
$year = filter_var($_POST['angkatan'] ?? null, FILTER_VALIDATE_INT);

if ($nim === '' || $name === '' || $email === '' || $program === '' || $year === false || $year === null) {
    fail(t('required_fields'));
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fail(t('invalid_email'));
}
if ($year < 1900 || $year > (int) date('Y') + 1) {
    fail(t('invalid_year'));
}
if (strlen($nim) > 30 || strlen($name) > 120 || strlen($email) > 190 || strlen($program) > 120) {
    fail(t('required_fields'));
}
if ($id === false) {
    fail(t('invalid_request'));
}

try {
    if ($id) {
        $statement = database()->prepare(
            'UPDATE mahasiswa SET nim = :nim, nama = :nama, email = :email, program_studi = :program, angkatan = :angkatan WHERE id = :id'
        );
        $statement->execute([
            'nim' => $nim, 'nama' => $name, 'email' => $email,
            'program' => $program, 'angkatan' => $year, 'id' => $id,
        ]);
    } else {
        $statement = database()->prepare(
            'INSERT INTO mahasiswa (nim, nama, email, program_studi, angkatan) VALUES (:nim, :nama, :email, :program, :angkatan)'
        );
        $statement->execute([
            'nim' => $nim, 'nama' => $name, 'email' => $email,
            'program' => $program, 'angkatan' => $year,
        ]);
    }
    fail(t('student_saved'), 'success');
} catch (PDOException $exception) {
    error_log('Student save database error: ' . $exception->getMessage());
    if ($exception->getCode() === '23000') {
        fail(t('duplicate_data'));
    }
    fail(t('save_failed'));
}
