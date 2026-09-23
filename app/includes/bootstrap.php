<?php
declare(strict_types=1);

// Session cookie: not readable from JavaScript, not sent with cross-site POSTs.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

/** One shared PDO connection with real prepared statements. */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = require __DIR__ . '/../config.php';
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}

/** Escape text before printing it into HTML (stops XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_is_valid(): bool
{
    $sent = $_POST['csrf'] ?? null;

    return is_string($sent) && hash_equals($_SESSION['csrf'] ?? '', $sent);
}

/** Store a one-time message, or read (and forget) the stored one. */
function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }

    $stored = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $stored;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function log_in(int $id, string $username): void
{
    // New session id after login prevents session fixation.
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $id, 'username' => $username];
}

/** Two-letter initials for the avatar, e.g. "alex_doe" -> "AD". */
function initials(string $name): string
{
    $parts = preg_split('/[\s_\-.]+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [$name];
    $letters = mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : mb_substr($parts[0], 1, 1));

    return mb_strtoupper($letters);
}
