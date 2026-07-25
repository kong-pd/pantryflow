<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

date_default_timezone_set(APP_TIMEZONE);
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('pantryflow_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE_URL . '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/config/pdo.php';
require_once APP_ROOT . '/includes/auth.php';

