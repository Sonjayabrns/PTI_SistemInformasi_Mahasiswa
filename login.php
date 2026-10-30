<?php
require_once __DIR__ . '/bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = t('invalid_request');
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        try {
            $statement = database()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
            $statement->execute(['username' => $username]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                redirect('index.php');
            }

            $error = t('login_failed');
        } catch (PDOException $exception) {
            error_log('Login database error: ' . $exception->getMessage());
            $error = t('db_error');
        }
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="<?= h($_SESSION['language'] ?? 'id') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(t('login_title')) ?> - <?= h(t('app_title')) ?></title>
    <style>
        body{font:16px Arial,sans-serif;background:#f3f6fa;color:#1f2937;margin:0}
        main{max-width:420px;margin:8vh auto;padding:28px;background:#fff;border-radius:10px;box-shadow:0 4px 18px #0001}
        label{display:block;margin:16px 0 6px}input{box-sizing:border-box;width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:5px}
        button{margin-top:20px;padding:10px 16px;background:#155eef;color:#fff;border:0;border-radius:5px;cursor:pointer}
        .error{background:#fee2e2;padding:10px;border-radius:5px}.language{float:right}
    </style>
</head>
<body>
<main>
    <div class="language">
        <a href="?lang=id"><?= h(t('indonesian')) ?></a> |
        <a href="?lang=en"><?= h(t('english')) ?></a>
    </div>
    <h1><?= h(t('login_title')) ?></h1>
    <?php if ($flash): ?><p class="error"><?= h($flash['message']) ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <label for="username"><?= h(t('username')) ?></label>
        <input id="username" name="username" type="text" autocomplete="username" required>
        <label for="password"><?= h(t('password')) ?></label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit"><?= h(t('login')) ?></button>
    </form>
</main>
</body>
</html>
