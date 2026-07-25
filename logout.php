<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_post()) {
    redirect(is_admin() ? 'dashboard.php' : 'login.php');
}

$_SESSION = [];
session_regenerate_id(true);
set_flash('success', 'You have been logged out.');
redirect('login.php');

