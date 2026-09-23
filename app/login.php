<?php
require __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$errors = [];
$notice = flash();
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $update = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $update->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }

            log_in((int) $user['id'], $user['username']);
            flash('You are logged in!');
            redirect('index.php');
        }

        // Same message for "no such user" and "wrong password", so nobody can guess usernames.
        $errors[] = 'Username or password is incorrect.';
    }
}

$pageTitle = 'Log in';
$pageDescription = 'Log in to the LAMP login and registration demo.';
require __DIR__ . '/includes/layout-top.php';
?>
        <section class="auth-card" aria-labelledby="form-title">
            <p class="eyebrow">Step 02 · Log in</p>
            <h1 id="form-title" class="auth-card__title">Welcome back</h1>

            <form method="post" action="login.php" class="form" novalidate>
                <?php require __DIR__ . '/includes/alerts.php'; ?>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required value="<?= e($username) ?>">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn--primary btn--block">
                    Log in <span class="btn__icon" aria-hidden="true">↗</span>
                </button>
            </form>

            <p class="auth-card__switch">New here? <a href="register.php">Create an account</a></p>
        </section>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
