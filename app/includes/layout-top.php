<?php
/** @var string $pageTitle */
/** @var string $pageDescription */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · LAMP Login</title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme-init.js"></script>
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="bg" aria-hidden="true"><div class="bg__dots"></div><div class="bg__glow"></div></div>

    <header class="site-header">
        <div class="container site-header__inner">
            <a class="brand" href="../index.html">
                <img src="../favicon.svg" alt="" width="28" height="28">
                <span>lamp<span class="brand__slash">/</span>login</span>
            </a>
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
                <span class="theme-toggle__icon" aria-hidden="true"></span>
            </button>
        </div>
    </header>

    <main id="main" class="auth-shell">
