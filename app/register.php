<?php
require __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$errors = [];
$notice = null;
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');

    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3–30 letters, numbers or underscores.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);

        foreach ($stmt->fetchAll() as $existing) {
            if (strcasecmp($existing['username'], $username) === 0) {
                $errors[] = 'That username is already taken.';
            }
            if (strcasecmp($existing['email'], $email) === 0) {
                $errors[] = 'That email is already registered.';
            }
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);

        log_in((int) db()->lastInsertId(), $username);
        flash('Your account is ready. Welcome aboard!');
        redirect('index.php');
    }
}

$pageTitle = 'Create account';
$pageDescription = 'Create an account in the LAMP login and registration demo.';
require __DIR__ . '/includes/layout-top.php';
?>
        <section class="auth-card" aria-labelledby="form-title">
            <p class="eyebrow">Step 01 · Register</p>
            <h1 id="form-title" class="auth-card__title">Create an account</h1>

            <form method="post" action="register.php" class="form" novalidate>
                <?php require __DIR__ . '/includes/alerts.php'; ?>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                           minlength="3" maxlength="30" value="<?= e($username) ?>">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="<?= e($email) ?>">
                </div>
                <div class="field">
                    <label for="password">Password <span class="field__hint">min. 8 characters</span></label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8">
                </div>
                <div class="field">
                    <label for="password_confirm">Repeat password</label>
                    <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required minlength="8">
                </div>

                <button type="submit" class="btn btn--primary btn--block">
                    Create account <span class="btn__icon" aria-hidden="true">↗</span>
                </button>
            </form>

            <p class="auth-card__switch">Already have an account? <a href="login.php">Log in</a></p>
        </section>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
