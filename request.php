<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    $clientName = trim((string) ($_POST['client_name'] ?? ''));
    $contact = trim((string) ($_POST['contact'] ?? ''));
    $pickupDateRaw = trim((string) ($_POST['pickup_date'] ?? ''));
    $foodItemIdRaw = trim((string) ($_POST['food_item_id'] ?? ''));
    $requestedQtyRaw = trim((string) ($_POST['requested_qty'] ?? ''));

    $oldInput = [
        'client_name' => $clientName,
        'contact' => $contact,
        'pickup_date' => $pickupDateRaw,
        'food_item_id' => $foodItemIdRaw,
        'requested_qty' => $requestedQtyRaw,
    ];

    $errors = [];

    if ($clientName === '' || mb_strlen($clientName) > 100) {
        $errors[] = 'Name is required and must not exceed 100 characters.';
    }

    if (!preg_match('/^\+?[0-9][0-9\s-]{7,19}$/', $contact)) {
        $errors[] = 'Enter a valid contact number using 8 to 20 digits and common separators.';
    }

    $pickupDate = parse_ymd_date($pickupDateRaw);
    $today = new DateTimeImmutable('today');
    if ($pickupDate === null || $pickupDate <= $today) {
        $errors[] = 'Pickup date must be later than today.';
    }

    $foodItemId = filter_var(
        $foodItemIdRaw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if ($foodItemId === false) {
        $errors[] = 'Select a valid food item.';
    }

    $requestedQty = filter_var(
        $requestedQtyRaw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if ($requestedQty === false) {
        $errors[] = 'Requested quantity must be a positive whole number.';
    }

    if ($errors !== []) {
        remember_form($oldInput);
        set_flash('error', implode(' ', $errors));
        redirect('request.php');
    }

    $connection = null;

    try {
        $connection = db();
        $connection->beginTransaction();

        $itemStatement = $connection->prepare(
            'SELECT id, name, quantity, expiry_date, is_active
             FROM food_items
             WHERE id = :id
             FOR UPDATE'
        );
        $itemStatement->execute(['id' => $foodItemId]);
        $item = $itemStatement->fetch();

        if ($item === false) {
            throw new DomainException('The selected food item no longer exists.');
        }

        if ((int) $item['is_active'] !== 1) {
            throw new DomainException('The selected food item is no longer available for requests.');
        }

        $expiryDate = $item['expiry_date'] !== null
            ? parse_ymd_date((string) $item['expiry_date'])
            : null;

        if ($expiryDate !== null && $expiryDate < $today) {
            throw new DomainException('The selected food item has expired and cannot be requested.');
        }

        if ((int) $item['quantity'] < $requestedQty) {
            throw new DomainException('The requested quantity is no longer available. Please choose a smaller amount.');
        }

        $insertStatement = $connection->prepare(
            'INSERT INTO client_requests
                (client_name, contact, pickup_date, food_item_id, requested_qty, status)
             VALUES
                (:client_name, :contact, :pickup_date, :food_item_id, :requested_qty, :status)'
        );
        $insertStatement->execute([
            'client_name' => $clientName,
            'contact' => $contact,
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'food_item_id' => $foodItemId,
            'requested_qty' => $requestedQty,
            'status' => 'pending',
        ]);

        $requestId = (int) $connection->lastInsertId();

        $updateStatement = $connection->prepare(
            'UPDATE food_items
             SET quantity = quantity - :decrement_qty
             WHERE id = :id
               AND quantity >= :minimum_qty'
        );
        $updateStatement->execute([
            'decrement_qty' => $requestedQty,
            'minimum_qty' => $requestedQty,
            'id' => $foodItemId,
        ]);

        if ($updateStatement->rowCount() !== 1) {
            throw new RuntimeException('Inventory update did not affect exactly one row.');
        }

        $connection->commit();

        remember_recent_request($requestId);

        redirect('request.php?confirmed=' . $requestId);
    } catch (DomainException $exception) {
        if ($connection instanceof PDO && $connection->inTransaction()) {
            $connection->rollBack();
        }

        remember_form($oldInput);
        set_flash('error', $exception->getMessage());
        redirect('request.php');
    } catch (Throwable $exception) {
        if ($connection instanceof PDO && $connection->inTransaction()) {
            $connection->rollBack();
        }

        error_log('Request processing failed: ' . $exception->getMessage());
        remember_form($oldInput);
        set_flash('error', 'The request could not be processed safely. Please try again.');
        redirect('request.php');
    }
}

$confirmation = null;
$confirmationIdRaw = $_GET['confirmed'] ?? null;

if ($confirmationIdRaw !== null) {
    $confirmationId = filter_var(
        $confirmationIdRaw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($confirmationId === false || !has_recent_request($confirmationId)) {
        set_flash('error', 'That pickup confirmation is not available in this browser session.');
        redirect(recent_request_ids() === [] ? 'request.php' : 'history.php');
    }

    try {
        $confirmationStatement = db()->prepare(
            'SELECT cr.id, cr.pickup_date, cr.requested_qty, cr.status, fi.name AS item_name
             FROM client_requests AS cr
             INNER JOIN food_items AS fi ON fi.id = cr.food_item_id
             WHERE cr.id = :id
             LIMIT 1'
        );
        $confirmationStatement->execute(['id' => $confirmationId]);
        $confirmationRecord = $confirmationStatement->fetch();

        if ($confirmationRecord === false) {
            forget_recent_request($confirmationId);
            set_flash('info', 'That pickup is no longer available in recent history.');
            redirect(recent_request_ids() === [] ? 'request.php' : 'history.php');
        }

        $confirmationDate = parse_ymd_date((string) $confirmationRecord['pickup_date']);

        if ($confirmationDate === null) {
            throw new RuntimeException('Stored pickup date could not be parsed.');
        }

        $confirmation = [
            'request_id' => (int) $confirmationRecord['id'],
            'item_name' => (string) $confirmationRecord['item_name'],
            'quantity' => (int) $confirmationRecord['requested_qty'],
            'pickup_date' => $confirmationDate,
            'status' => (string) $confirmationRecord['status'],
        ];
    } catch (Throwable $exception) {
        error_log('Confirmation load failed: ' . $exception->getMessage());
        set_flash('error', 'The pickup confirmation is temporarily unavailable.');
        redirect('history.php');
    }
}

if ($confirmation !== null) {
    $isRejected = $confirmation['status'] === 'rejected';
    $pageTitle = $isRejected ? 'Pickup request declined' : 'Pickup request received';
    $pageId = 'request';
    require APP_ROOT . '/includes/header.php';
    ?>

    <section class="confirmation-section" aria-labelledby="confirmation-title">
        <div class="container confirmation-shell" role="status">
            <span class="confirmation-mark <?= $isRejected ? 'confirmation-mark-rejected' : '' ?>" aria-hidden="true"><?= $isRejected ? '&times;' : '&#10003;' ?></span>
            <p class="eyebrow"><?= $isRejected ? 'Request declined' : 'Request received' ?></p>
            <h1 id="confirmation-title"><?= $isRejected ? 'This pickup will not proceed.' : 'Your request is with the pantry team.' ?></h1>
            <p class="confirmation-lead">
                <?php if ($isRejected): ?>
                    The pantry team could not fulfil <?= e($confirmation['quantity']) ?> unit<?= $confirmation['quantity'] === 1 ? '' : 's' ?> of
                    <?= e($confirmation['item_name']) ?> for <?= e($confirmation['pickup_date']->format('d M Y')) ?>.
                <?php else: ?>
                    <?= e($confirmation['quantity']) ?> unit<?= $confirmation['quantity'] === 1 ? '' : 's' ?> of
                    <?= e($confirmation['item_name']) ?> <?= $confirmation['quantity'] === 1 ? 'is' : 'are' ?> reserved while the request is reviewed for
                    <?= e($confirmation['pickup_date']->format('d M Y')) ?>.
                <?php endif; ?>
            </p>

            <dl class="confirmation-details" aria-label="Pickup request details">
                <div>
                    <dt>Item</dt>
                    <dd><?= e($confirmation['item_name']) ?></dd>
                </div>
                <div>
                    <dt>Quantity</dt>
                    <dd><?= e($confirmation['quantity']) ?> unit<?= $confirmation['quantity'] === 1 ? '' : 's' ?></dd>
                </div>
                <div>
                    <dt>Pickup</dt>
                    <dd><?= e($confirmation['pickup_date']->format('d M Y')) ?></dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd><?= $isRejected ? 'Rejected' : 'Pending' ?></dd>
                </div>
            </dl>

            <div class="confirmation-actions">
                <a class="button button-primary" href="<?= e(url('history.php')) ?>">View recent pickups</a>
                <a class="text-link" href="<?= e(url('request.php')) ?>">Arrange another pickup <span aria-hidden="true">&rarr;</span></a>
            </div>
            <p class="confirmation-note">
                Reference PF-<?= e(str_pad((string) $confirmation['request_id'], 4, '0', STR_PAD_LEFT)) ?>
                <span aria-hidden="true">&middot;</span> Saved in this browser session
            </p>
        </div>
    </section>

    <?php
    require APP_ROOT . '/includes/footer.php';
    exit;
}

$oldInput = consume_form();
$items = [];
$loadError = null;

try {
    $itemStatement = db()->query(
        'SELECT id, name, quantity, expiry_date
         FROM food_items
         WHERE quantity > 0
           AND is_active = 1
           AND (expiry_date IS NULL OR expiry_date >= CURRENT_DATE)
         ORDER BY name ASC'
    );
    $items = $itemStatement->fetchAll();
} catch (Throwable $exception) {
    error_log('Request item load failed: ' . $exception->getMessage());
    $loadError = 'Requestable items are temporarily unavailable.';
}

if (!isset($oldInput['food_item_id']) && isset($_GET['item'])) {
    $requestedItemId = filter_var(
        $_GET['item'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($requestedItemId !== false) {
        foreach ($items as $availableItem) {
            if ((int) $availableItem['id'] === $requestedItemId) {
                $oldInput['food_item_id'] = (string) $requestedItemId;
                break;
            }
        }
    }
}

$pageTitle = 'Request food';
$pageId = 'request';
require APP_ROOT . '/includes/header.php';
?>

<section class="page-heading request-heading">
    <div class="container">
        <a class="back-link" href="<?= e(url('index.php#inventory')) ?>"><span aria-hidden="true">&larr;</span> Back to inventory</a>
        <p class="eyebrow">Arrange pantry assistance</p>
        <h1>Plan your pickup.</h1>
        <p>Select an available item, choose a future date, then leave contact details for the pantry team.</p>
    </div>
</section>

<section class="request-progress" aria-label="Request progress">
    <div class="container">
        <ol>
            <li class="is-current"><span>01</span><div><strong>Select</strong><small>Item &amp; availability</small></div></li>
            <li><span>02</span><div><strong>Arrange</strong><small>Pickup &amp; contact</small></div></li>
            <li><span>03</span><div><strong>Confirmation</strong><small>Stock reserved</small></div></li>
        </ol>
    </div>
</section>

<section class="section section-tight">
    <div class="container request-layout">
        <div class="form-card">
            <?php if ($loadError !== null): ?>
                <div class="empty-state empty-state-error" role="alert">
                    <h2>Form unavailable</h2>
                    <p><?= e($loadError) ?></p>
                </div>
            <?php elseif ($items === []): ?>
                <div class="empty-state">
                    <h2>No items can be requested</h2>
                    <p>All current items are expired or out of stock.</p>
                    <a class="button button-secondary" href="<?= e(url('index.php#inventory')) ?>">Review inventory</a>
                </div>
            <?php else: ?>
                <form id="request-form" action="<?= e(url('request.php')) ?>" method="post" novalidate>
                    <div class="form-card-heading">
                        <div>
                            <p class="eyebrow">Pickup details</p>
                            <h2>Your arrangement</h2>
                        </div>
                        <span class="required-note"><span aria-hidden="true">*</span> Required</span>
                    </div>

                    <section class="form-section" aria-labelledby="provision-section-title">
                        <h3 id="provision-section-title" class="form-section-title"><span>01</span> Select your provision</h3>
                        <p class="form-section-copy">Begin with the item, quantity and preferred pickup date.</p>
                        <div class="field-grid">
                        <div class="field field-full">
                            <label for="food_item_id">Available item <span aria-hidden="true">*</span></label>
                            <select id="food_item_id" name="food_item_id" required aria-describedby="availability-hint food_item_id_error">
                                <option value="">Choose from today&apos;s pantry</option>
                                <?php foreach ($items as $item): ?>
                                    <option
                                        value="<?= e($item['id']) ?>"
                                        data-quantity="<?= e($item['quantity']) ?>"
                                        data-name="<?= e($item['name']) ?>"
                                        <?= (string) ($oldInput['food_item_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>
                                    >
                                        <?= e($item['name']) ?> &mdash; <?= e($item['quantity']) ?> available
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p id="availability-hint" class="field-hint" aria-live="polite">Select an item to view the maximum request quantity.</p>
                            <p id="food_item_id_error" class="field-error" data-error-for="food_item_id" aria-live="polite"></p>
                        </div>

                        <div class="field">
                            <label for="requested_qty">Quantity <span aria-hidden="true">*</span></label>
                            <input id="requested_qty" name="requested_qty" type="number" min="1" step="1" inputmode="numeric" required aria-describedby="requested_qty_error" value="<?= e($oldInput['requested_qty'] ?? '1') ?>">
                            <p id="requested_qty_error" class="field-error" data-error-for="requested_qty" aria-live="polite"></p>
                        </div>

                        <div class="field">
                            <label for="pickup_date">Pickup date <span aria-hidden="true">*</span></label>
                            <input id="pickup_date" name="pickup_date" type="date" required aria-describedby="pickup_date_error" value="<?= e($oldInput['pickup_date'] ?? '') ?>">
                            <p id="pickup_date_error" class="field-error" data-error-for="pickup_date" aria-live="polite"></p>
                        </div>
                        </div>
                    </section>

                    <section class="form-section" aria-labelledby="contact-section-title">
                        <h3 id="contact-section-title" class="form-section-title"><span>02</span> Add contact details</h3>
                        <p class="form-section-copy">Use the details the pantry team can reach before pickup.</p>
                        <div class="field-grid">
                        <div class="field field-full">
                            <label for="client_name">Full name <span aria-hidden="true">*</span></label>
                            <input id="client_name" name="client_name" type="text" maxlength="100" autocomplete="name" required aria-describedby="client_name_error" value="<?= e($oldInput['client_name'] ?? '') ?>">
                            <p id="client_name_error" class="field-error" data-error-for="client_name" aria-live="polite"></p>
                        </div>

                        <div class="field field-full">
                            <label for="contact">Contact number <span aria-hidden="true">*</span></label>
                            <input id="contact" name="contact" type="tel" maxlength="20" autocomplete="tel" inputmode="tel" placeholder="e.g. +60 12-345 6789" required aria-describedby="contact_hint contact_error" value="<?= e($oldInput['contact'] ?? '') ?>">
                            <p id="contact_hint" class="field-hint">Include the country code if the pantry may call from another area.</p>
                            <p id="contact_error" class="field-error" data-error-for="contact" aria-live="polite"></p>
                        </div>
                        </div>
                    </section>

                    <div class="form-submit-row">
                        <div>
                            <strong>Review your arrangement</strong>
                            <span>Confirmation follows after one final live stock check.</span>
                        </div>
                        <button id="request-submit" class="button button-primary" type="submit">Confirm pickup request <span aria-hidden="true">&rarr;</span></button>
                    </div>
                    <p id="form-status" class="sr-only" aria-live="polite"></p>
                </form>
            <?php endif; ?>
        </div>

        <aside class="request-sidebar" aria-label="Pickup summary and guidance">
            <div class="request-summary-card" aria-live="polite">
                <div class="summary-heading">
                    <div>
                        <p class="eyebrow">Your itinerary</p>
                        <h2>Pickup summary</h2>
                    </div>
                    <span class="summary-reference">PF / PENDING</span>
                </div>
                <dl class="itinerary-list">
                    <div>
                        <dt>Item</dt>
                        <dd id="selected-item-name">Not selected</dd>
                    </div>
                    <div>
                        <dt>Quantity</dt>
                        <dd id="selected-quantity">1 unit</dd>
                    </div>
                    <div>
                        <dt>Pickup date</dt>
                        <dd id="selected-pickup-date">Not selected</dd>
                    </div>
                    <div>
                        <dt>Availability</dt>
                        <dd id="selected-item-availability">Select an item to check.</dd>
                    </div>
                </dl>
            </div>
            <div class="information-panel">
                <p class="eyebrow">Pantry concierge</p>
                <h2>What follows</h2>
                <ol class="numbered-list">
                    <li><span>01</span><div><strong>Live stock check</strong><p>The latest quantity is verified.</p></div></li>
                    <li><span>02</span><div><strong>Secure reservation</strong><p>Your request and inventory update together.</p></div></li>
                    <li><span>03</span><div><strong>Clear confirmation</strong><p>Your item and date appear on screen.</p></div></li>
                </ol>
            </div>
        </aside>
    </div>
</section>

<script src="<?= e(url('assets/js/request-validation.js')) ?>" defer></script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
