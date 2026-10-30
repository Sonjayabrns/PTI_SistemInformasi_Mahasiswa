<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

try {
    $students = database()->query('SELECT id, nim, nama, email, program_studi, angkatan FROM mahasiswa ORDER BY id DESC')->fetchAll();
} catch (PDOException $exception) {
    error_log('Student list database error: ' . $exception->getMessage());
    http_response_code(500);
    $students = [];
    $databaseError = t('db_error');
}

$editStudent = null;
if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
    try {
        $statement = database()->prepare('SELECT id, nim, nama, email, program_studi, angkatan FROM mahasiswa WHERE id = :id');
        $statement->execute(['id' => (int) $_GET['edit']]);
        $editStudent = $statement->fetch() ?: null;
    } catch (PDOException $exception) {
        error_log('Student edit lookup database error: ' . $exception->getMessage());
        $databaseError = t('db_error');
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$student = $editStudent ?: ['id' => '', 'nim' => '', 'nama' => '', 'email' => '', 'program_studi' => '', 'angkatan' => ''];
?>
<!DOCTYPE html>
<html lang="<?= h($_SESSION['language'] ?? 'id') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(t('app_title')) ?></title>
    <style>
        body{font:16px Arial,sans-serif;background:#f3f6fa;color:#1f2937;margin:0}
        main{max-width:1100px;margin:30px auto;padding:24px;background:#fff;border-radius:10px;box-shadow:0 4px 18px #0001}
        header{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
        form.inline{display:inline}label{display:block;margin:12px 0 5px}input{box-sizing:border-box;width:100%;max-width:480px;padding:9px;border:1px solid #cbd5e1;border-radius:5px}
        button,.button{display:inline-block;padding:8px 12px;border:0;border-radius:5px;background:#155eef;color:#fff;text-decoration:none;cursor:pointer}
        .danger{background:#b42318}.muted{background:#64748b}.notice{padding:10px;border-radius:5px;background:#dcfce7}.error{background:#fee2e2}
        table{width:100%;border-collapse:collapse;margin-top:16px}th,td{text-align:left;padding:10px;border-bottom:1px solid #e2e8f0}th{background:#f8fafc}
        .table-wrap{overflow-x:auto}.language{white-space:nowrap}
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1><?= h(t('app_title')) ?></h1>
            <p><?= h(t('welcome')) ?>, <?= h((string) $_SESSION['username']) ?></p>
        </div>
        <div class="language">
            <?= h(t('language')) ?>:
            <a href="?lang=id"><?= h(t('indonesian')) ?></a> |
            <a href="?lang=en"><?= h(t('english')) ?></a>
            <form class="inline" method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button class="muted" type="submit"><?= h(t('logout')) ?></button>
            </form>
        </div>
    </header>

    <?php if (!empty($databaseError)): ?><p class="notice error"><?= h($databaseError) ?></p><?php endif; ?>
    <?php if ($flash): ?><p class="notice <?= $flash['type'] === 'error' ? 'error' : '' ?>"><?= h($flash['message']) ?></p><?php endif; ?>

    <section>
        <h2><?= h($editStudent ? t('edit_student') : t('add_student')) ?></h2>
        <form method="post" action="proses.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= h((string) $student['id']) ?>">
            <label for="nim"><?= h(t('nim')) ?></label>
            <input id="nim" name="nim" type="text" maxlength="30" value="<?= h((string) $student['nim']) ?>" required>
            <label for="nama"><?= h(t('name')) ?></label>
            <input id="nama" name="nama" type="text" maxlength="120" value="<?= h((string) $student['nama']) ?>" required>
            <label for="email"><?= h(t('email')) ?></label>
            <input id="email" name="email" type="email" maxlength="190" value="<?= h((string) $student['email']) ?>" required>
            <label for="program_studi"><?= h(t('program')) ?></label>
            <input id="program_studi" name="program_studi" type="text" maxlength="120" value="<?= h((string) $student['program_studi']) ?>" required>
            <label for="angkatan"><?= h(t('year')) ?></label>
            <input id="angkatan" name="angkatan" type="number" min="1900" max="<?= (int) date('Y') + 1 ?>" value="<?= h((string) $student['angkatan']) ?>" required>
            <p><button type="submit"><?= h(t('save')) ?></button>
            <?php if ($editStudent): ?> <a class="button muted" href="index.php"><?= h(t('cancel')) ?></a><?php endif; ?></p>
        </form>
    </section>

    <section>
        <h2><?= h(t('dashboard')) ?></h2>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th><?= h(t('nim')) ?></th><th><?= h(t('name')) ?></th><th><?= h(t('email')) ?></th>
                    <th><?= h(t('program')) ?></th><th><?= h(t('year')) ?></th><th><?= h(t('actions')) ?></th>
                </tr></thead>
                <tbody>
                <?php if (!$students): ?>
                    <tr><td colspan="6"><?= h(t('no_students')) ?></td></tr>
                <?php else: foreach ($students as $row): ?>
                    <tr>
                        <td><?= h($row['nim']) ?></td><td><?= h($row['nama']) ?></td><td><?= h($row['email']) ?></td>
                        <td><?= h($row['program_studi']) ?></td><td><?= h((string) $row['angkatan']) ?></td>
                        <td>
                            <a class="button" href="?edit=<?= (int) $row['id'] ?>"><?= h(t('edit')) ?></a>
                            <form class="inline" method="post" action="proses.php" onsubmit="return confirm(<?= h(json_encode(t('confirm_delete'), JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button class="danger" type="submit"><?= h(t('delete')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
