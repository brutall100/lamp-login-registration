<?php
require __DIR__ . '/includes/bootstrap.php';

$sessionUser = current_user();

if (!$sessionUser) {
    flash('Please log in first.');
    redirect('login.php');
}

$stmt = db()->prepare('SELECT username, email, created_at FROM users WHERE id = ?');
$stmt->execute([$sessionUser['id']]);
$user = $stmt->fetch();

if (!$user) {
    // The account was deleted while the session was still open.
    $_SESSION = [];
    redirect('login.php');
}

$errors = [];
$notice = flash();

$pageTitle = 'Dashboard';
$pageDescription = 'Your account dashboard in the LAMP login and registration demo.';
require __DIR__ . '/includes/layout-top.php';
?>
        <section class="auth-card" aria-labelledby="form-title">
            <p class="eyebrow">Step 03 · Session active</p>
            <?php require __DIR__ . '/includes/alerts.php'; ?>

            <div class="profile">
                <span class="avatar" aria-hidden="true"><?= e(initials($user['username'])) ?></span>
                <div>
                    <h1 id="form-title" class="auth-card__title">Hi, <?= e($user['username']) ?></h1>
                    <p class="profile__meta"><?= e($user['email']) ?></p>
                </div>
            </div>

            <dl class="record">
                <div><dt>Member since</dt><dd><?= e(date('M j, Y', strtotime($user['created_at']))) ?></dd></div>
                <div><dt>Password</dt><dd>bcrypt hash · never stored as text</dd></div>
                <div><dt>Session</dt><dd>HttpOnly cookie · SameSite=Lax</dd></div>
            </dl>

            <form method="post" action="logout.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn--ghost btn--block">
                    Log out <span class="btn__icon" aria-hidden="true">↗</span>
                </button>
            </form>
        </section>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
