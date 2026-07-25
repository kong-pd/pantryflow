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
            'SELECT id, name, quantity, expiry_date
             FROM food_items
             WHERE id = :id
             FOR UPDATE'
        );
        $itemStatement->execute(['id' => $foodItemId]);
        $item = $itemStatement->fetch();

        if ($item === false) {
            throw new DomainException('The selected food item no longer exists.');
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
                (client_name, contact, pickup_date, food_item_id, requested_qty)
             VALUES
                (:client_name, :contact, :pickup_date, :food_item_id, :requested_qty)'
        );
        $insertStatement->execute([
            'client_name' => $clientName,
            'contact' => $contact,
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'food_item_id' => $foodItemId,
            'requested_qty' => $requestedQty,
        ]);

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
        set_flash(
            'success',
            sprintf(
                'Request confirmed for %d unit%s of %s. Pickup date: %s.',
                $requestedQty,
                $requestedQty === 1 ? '' : 's',
                $item['name'],
                $pickupDate->format('d M Y')
            )
        );
        redirect('request.php');
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

$oldInput = consume_form();
$items = [];
$loadError = null;

try {
    $itemStatement = db()->query(
        'SELECT id, name, quantity, expiry_date
         FROM food_items
         WHERE quantity > 0
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

<section class="page-heading">
    <div class="container narrow">
        <a class="back-link" href="<?= e(url('index.php#inventory')) ?>"><span aria-hidden="true">&larr;</span> Back to inventory</a>
        <p class="eyebrow">Pickup request</p>
        <h1>Tell us what you need.</h1>
        <p>Complete the form once. PantryFlow checks live stock before confirming your request.</p>
    </div>
</section>

<section class="request-progress" aria-label="Request progress">
    <div class="container narrow">
        <ol>
            <li class="is-current"><span>1</span> Enter details</li>
            <li><span>2</span> Stock check</li>
            <li><span>3</span> Confirmation</li>
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
                            <p class="eyebrow">Request details</p>
                            <h2>One item, one pickup</h2>
                        </div>
                        <span class="required-note"><span aria-hidden="true">*</span> All fields required</span>
                    </div>

                    <fieldset class="form-section">
                        <legend><span>1</span> Your details</legend>
                        <p class="form-section-copy">Use contact details the pantry can reach before pickup.</p>
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
                    </fieldset>

                    <fieldset class="form-section">
                        <legend><span>2</span> Item &amp; pickup</legend>
                        <p class="form-section-copy">Available quantity updates when you choose an item.</p>
                        <div class="field-grid">
                        <div class="field field-full">
                            <label for="food_item_id">Food item <span aria-hidden="true">*</span></label>
                            <select id="food_item_id" name="food_item_id" required aria-describedby="availability-hint food_item_id_error">
                                <option value="">Select an available item</option>
                                <?php foreach ($items as $item): ?>
                                    <option
                                        value="<?= e($item['id']) ?>"
                                        data-quantity="<?= e($item['quantity']) ?>"
                                        data-name="<?= e($item['name']) ?>"
                                        <?= (string) ($oldInput['food_item_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>
                                    >
                                        <?= e($item['name']) ?> — <?= e($item['quantity']) ?> available
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p id="availability-hint" class="field-hint" aria-live="polite">Choose an item to see the maximum request quantity.</p>
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
                    </fieldset>

                    <div class="form-submit-row">
                        <div>
                            <strong>Ready to send?</strong>
                            <span>Your request is confirmed only after the server checks stock.</span>
                        </div>
                        <button id="request-submit" class="button button-primary" type="submit">Confirm request <span aria-hidden="true">&rarr;</span></button>
                    </div>
                    <p id="form-status" class="sr-only" aria-live="polite"></p>
                </form>
            <?php endif; ?>
        </div>

        <aside class="request-sidebar" aria-label="Request guidance">
            <div class="request-summary-card" aria-live="polite" aria-atomic="true">
                <p class="eyebrow">Your selection</p>
                <h2 id="selected-item-name">No item selected</h2>
                <p id="selected-item-availability">Choose an item in the form to view current availability.</p>
            </div>
            <div class="information-panel">
                <h2>What happens next</h2>
                <ol class="numbered-list">
                    <li><span>1</span><div><strong>We check stock</strong><p>The latest quantity is checked again.</p></div></li>
                    <li><span>2</span><div><strong>Your request is saved</strong><p>Stock and request data update together.</p></div></li>
                    <li><span>3</span><div><strong>You see confirmation</strong><p>Keep the pickup date for your record.</p></div></li>
                </ol>
            </div>
            <p class="privacy-note"><strong>Privacy note:</strong> Contact details appear only in the protected administrator dashboard.</p>
        </aside>
    </div>
</section>

<script src="<?= e(url('assets/js/request-validation.js')) ?>" defer></script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
