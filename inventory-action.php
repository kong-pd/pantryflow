<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if (!is_post()) {
    redirect('dashboard.php#inventory');
}

$itemId = filter_var(
    $_POST['item_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$action = (string) ($_POST['action'] ?? '');

if ($itemId === false || !in_array($action, ['archive', 'restore', 'delete'], true)) {
    set_flash('error', 'Choose a valid inventory action.');
    redirect('dashboard.php#inventory');
}

$connection = null;

try {
    $connection = db();
    $connection->beginTransaction();

    $itemStatement = $connection->prepare(
        'SELECT id, name, is_active
         FROM food_items
         WHERE id = :id
         FOR UPDATE'
    );
    $itemStatement->execute(['id' => $itemId]);
    $item = $itemStatement->fetch();

    if ($item === false) {
        throw new DomainException('That inventory item no longer exists.');
    }

    if ($action === 'archive') {
        if ((int) $item['is_active'] === 1) {
            $statement = $connection->prepare(
                'UPDATE food_items
                 SET is_active = 0, archived_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND is_active = 1'
            );
            $statement->execute(['id' => $itemId]);
        }
    } elseif ($action === 'restore') {
        if ((int) $item['is_active'] === 0) {
            $statement = $connection->prepare(
                'UPDATE food_items
                 SET is_active = 1, archived_at = NULL
                 WHERE id = :id AND is_active = 0'
            );
            $statement->execute(['id' => $itemId]);
        }
    } else {
        if ((int) $item['is_active'] === 1) {
            throw new DomainException('Archive an item before deleting it permanently.');
        }

        $referenceStatement = $connection->prepare(
            'SELECT COUNT(*)
             FROM client_requests
             WHERE food_item_id = :id'
        );
        $referenceStatement->execute(['id' => $itemId]);

        if ((int) $referenceStatement->fetchColumn() !== 0) {
            throw new DomainException('This item is part of the request history and cannot be permanently deleted.');
        }

        $deleteStatement = $connection->prepare(
            'DELETE FROM food_items
             WHERE id = :id AND is_active = 0'
        );
        $deleteStatement->execute(['id' => $itemId]);

        if ($deleteStatement->rowCount() !== 1) {
            throw new RuntimeException('Inventory deletion did not affect exactly one row.');
        }
    }

    $connection->commit();
    redirect('dashboard.php#inventory');
} catch (DomainException $exception) {
    if ($connection instanceof PDO && $connection->inTransaction()) {
        $connection->rollBack();
    }

    set_flash('error', $exception->getMessage());
} catch (Throwable $exception) {
    if ($connection instanceof PDO && $connection->inTransaction()) {
        $connection->rollBack();
    }

    error_log('Inventory lifecycle action failed: ' . $exception->getMessage());
    set_flash('error', 'The inventory item could not be updated safely. Please try again.');
}

redirect('dashboard.php#inventory');

