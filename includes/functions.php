<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 302);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => in_array($type, ['success', 'error', 'info'], true) ? $type : 'info',
        'message' => $message,
    ];
}

function consume_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function remember_form(array $values, string $key = 'form_old'): void
{
    $_SESSION[$key] = $values;
}

function consume_form(string $key = 'form_old'): array
{
    $values = $_SESSION[$key] ?? [];
    unset($_SESSION[$key]);

    return is_array($values) ? $values : [];
}

function parse_ymd_date(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();

    if ($date === false) {
        return null;
    }

    if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
        return null;
    }

    return $date;
}

function format_date(?string $value): string
{
    if ($value === null || $value === '') {
        return 'No expiry date';
    }

    $date = parse_ymd_date($value);
    return $date ? $date->format('d M Y') : 'Invalid date';
}

function item_status(array $item): array
{
    $quantity = (int) ($item['quantity'] ?? 0);
    $expiryDate = isset($item['expiry_date']) && $item['expiry_date'] !== null
        ? parse_ymd_date((string) $item['expiry_date'])
        : null;
    $today = new DateTimeImmutable('today');

    if ($expiryDate !== null && $expiryDate < $today) {
        return ['label' => 'Expired', 'class' => 'status-expired'];
    }

    if ($quantity === 0) {
        return ['label' => 'Out of stock', 'class' => 'status-out'];
    }

    if ($quantity < 5) {
        return ['label' => 'Low stock', 'class' => 'status-low'];
    }

    return ['label' => 'Available', 'class' => 'status-available'];
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
