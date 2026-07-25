<?php

declare(strict_types=1);

function is_admin(): bool
{
    return isset($_SESSION['admin_username'])
        && hash_equals(ADMIN_USERNAME, (string) $_SESSION['admin_username']);
}

function require_admin(): void
{
    if (is_admin()) {
        return;
    }

    set_flash('error', 'Please log in to access the administrator dashboard.');
    redirect('login.php');
}

