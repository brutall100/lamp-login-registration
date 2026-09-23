<?php
// Database settings. Defaults match a fresh XAMPP / LAMP install.
// Override any of them with environment variables (DB_DSN, DB_USER, DB_PASS).
return [
    'dsn'  => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=registration;charset=utf8mb4',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
];
