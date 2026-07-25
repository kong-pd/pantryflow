<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if (!is_post()) {
    redirect('dashboard.php#requests-title');
}

$requestId = filter_var(
    $_POST['request_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($requestId === false) {
    set_flash('error', 'Choose a valid pickup request.');
    redirect('dashboard.php#requests-title');
}

$connection = null;

try {
    $connection = db();
    $connection->beginTransaction();

    $requestStatement = $connection->prepare(
        'SELECT id, food_item_id, requested_qty, status
         FROM client_requests
         WHERE id = :id
         FOR UPDATE'
    );
    $requestStatement->execute(['id' => $requestId]);
    $request = $requestStatement->fetch();

    if ($request === false) {
        throw new DomainException('That pickup request no longer exists.');
    }

    if ($request['status'] === 'rejected') {
        $connection->commit();
        redirect('dashboard.php#requests-title');
    }

    if ($request['status'] !== 'pending') {
        throw new DomainException('Only pending pickup requests can be rejected.');
    }

    $statusStatement = $connection->prepare(
        'UPDATE client_requests
         SET status = :status, reviewed_at = CURRENT_TIMESTAMP
         WHERE id = :id AND status = :current_status'
    );
    $statusStatement->execute([
        'status' => 'rejected',
        'id' => $requestId,
        'current_status' => 'pending',
    ]);

    if ($statusStatement->rowCount() !== 1) {
        throw new RuntimeException('Request status update did not affect exactly one row.');
    }

    $stockStatement = $connection->prepare(
        'UPDATE food_items
         SET quantity = quantity + :restored_qty
         WHERE id = :id'
    );
    $stockStatement->execute([
        'restored_qty' => (int) $request['requested_qty'],
        'id' => (int) $request['food_item_id'],
    ]);

    if ($stockStatement->rowCount() !== 1) {
        throw new RuntimeException('Inventory restoration did not affect exactly one row.');
    }

    $connection->commit();
    redirect('dashboard.php#requests-title');
} catch (DomainException $exception) {
    if ($connection instanceof PDO && $connection->inTransaction()) {
        $connection->rollBack();
    }

    set_flash('error', $exception->getMessage());
} catch (Throwable $exception) {
    if ($connection instanceof PDO && $connection->inTransaction()) {
        $connection->rollBack();
    }

    error_log('Reject request failed: ' . $exception->getMessage());
    set_flash('error', 'The pickup request could not be updated safely. Please try again.');
}

redirect('dashboard.php#requests-title');

