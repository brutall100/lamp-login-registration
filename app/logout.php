<?php
require __DIR__ . '/includes/bootstrap.php';

// Logging out changes state, so only accept a POST with a valid CSRF token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid()) {
    redirect('index.php');
}

$_SESSION = [];
session_regenerate_id(true);

flash('You have logged out.');
redirect('login.php');
