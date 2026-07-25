<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if (!is_post()) {
    redirect('dashboard.php');
}

$name = trim((string) ($_POST['name'] ?? ''));
$quantityRaw = trim((string) ($_POST['quantity'] ?? ''));
$expiryDateRaw = trim((string) ($_POST['expiry_date'] ?? ''));
$oldInput = [
    'name' => $name,
    'quantity' => $quantityRaw,
    'expiry_date' => $expiryDateRaw,
];
$errors = [];

if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Item name is required and must not exceed 100 characters.';
}

$quantity = filter_var(
    $quantityRaw,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 0]]
);
if ($quantity === false) {
    $errors[] = 'Quantity must be a non-negative whole number.';
}

$expiryDate = null;
if ($expiryDateRaw !== '') {
    $expiryDate = parse_ymd_date($expiryDateRaw);
    if ($expiryDate === null) {
        $errors[] = 'Enter a valid expiry date or leave it blank.';
    }
}

if ($errors !== []) {
    remember_form($oldInput, 'add_item_old');
    set_flash('error', implode(' ', $errors));
    redirect('dashboard.php');
}

try {
    $statement = db()->prepare(
        'INSERT INTO food_items (name, quantity, expiry_date)
         VALUES (:name, :quantity, :expiry_date)'
    );
    $statement->execute([
        'name' => $name,
        'quantity' => $quantity,
        'expiry_date' => $expiryDate?->format('Y-m-d'),
    ]);

    set_flash('success', sprintf('%s was added to the pantry inventory.', $name));
} catch (Throwable $exception) {
    error_log('Add item failed: ' . $exception->getMessage());
    remember_form($oldInput, 'add_item_old');
    set_flash('error', 'The food item could not be added safely. Please try again.');
}

redirect('dashboard.php');
