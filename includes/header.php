<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME;
$pageId = $pageId ?? '';
$flash = consume_flash();
$flashType = $flash['type'] ?? 'info';
$flashRole = $flashType === 'error' ? 'alert' : 'status';
$hasRecentRequests = recent_request_ids() !== [];
$styleVersion = (string) filemtime(APP_ROOT . '/assets/css/style.css');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PantryFlow community food pantry inventory and client request system.">
    <meta name="theme-color" content="#24312c">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css?v=' . $styleVersion)) ?>">
</head>
<body class="page-<?= e($pageId) ?>">
<a class="skip-link" href="#main-content">Skip to main content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="PantryFlow home">
            <span class="brand-mark" aria-hidden="true">PF</span>
            <span class="brand-copy">
                <strong>PANTRYFLOW</strong>
                <small>Community Pantry Service</small>
            </span>
        </a>

        <nav class="primary-nav" aria-label="Main navigation">
            <a class="<?= $pageId === 'home' ? 'is-active' : '' ?>" href="<?= e(url('index.php#inventory')) ?>" <?= $pageId === 'home' ? 'aria-current="page"' : '' ?>>Availability</a>
            <?php if ($hasRecentRequests && !is_admin()): ?>
                <a class="<?= $pageId === 'history' ? 'is-active' : '' ?>" href="<?= e(url('history.php')) ?>" <?= $pageId === 'history' ? 'aria-current="page"' : '' ?>>My pickups</a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
                <a class="<?= $pageId === 'dashboard' ? 'is-active' : '' ?>" href="<?= e(url('dashboard.php')) ?>" <?= $pageId === 'dashboard' ? 'aria-current="page"' : '' ?>>Operations</a>
                <form class="nav-form" action="<?= e(url('logout.php')) ?>" method="post">
                    <button class="nav-button" type="submit">Log out</button>
                </form>
            <?php else: ?>
                <a class="nav-reserve <?= $pageId === 'request' ? 'is-active' : '' ?>" href="<?= e(url('request.php')) ?>" <?= $pageId === 'request' ? 'aria-current="page"' : '' ?>>Request a pickup</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if ($flash !== null): ?>
    <div class="container flash-wrap">
        <div class="flash flash-<?= e($flashType) ?>" role="<?= e($flashRole) ?>" aria-live="<?= $flashType === 'error' ? 'assertive' : 'polite' ?>">
            <span class="flash-mark" aria-hidden="true"><?= $flashType === 'success' ? '&#10003;' : ($flashType === 'error' ? '!' : 'i') ?></span>
            <?= e($flash['message'] ?? '') ?>
        </div>
    </div>
<?php endif; ?>

<main id="main-content" tabindex="-1">
