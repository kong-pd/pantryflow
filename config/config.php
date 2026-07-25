<?php

declare(strict_types=1);

define('APP_NAME', 'PantryFlow');
define('APP_ROOT', dirname(__DIR__));
define('APP_BASE_URL', '/pantryflow');
define('APP_TIMEZONE', 'Asia/Kuala_Lumpur');

define('ADMIN_USERNAME', 'pantry_admin');
define('ADMIN_PASSWORD', 'help2026');

define('DB_HOST', getenv('PANTRYFLOW_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('PANTRYFLOW_DB_PORT') ?: '3306');
define('DB_NAME', getenv('PANTRYFLOW_DB_NAME') ?: 'pantryflow');
define('DB_USER', getenv('PANTRYFLOW_DB_USER') ?: 'root');

$databasePassword = getenv('PANTRYFLOW_DB_PASSWORD');
define('DB_PASSWORD', $databasePassword === false ? '' : $databasePassword);

$debugValue = getenv('PANTRYFLOW_DEBUG');
define(
    'APP_DEBUG',
    $debugValue !== false && filter_var($debugValue, FILTER_VALIDATE_BOOLEAN)
);

