<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$items = [];
$loadError = null;

try {
    $statement = db()->query(
        'SELECT id, name, quantity, expiry_date
         FROM food_items
         ORDER BY name ASC'
    );
    $items = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log('Inventory load failed: ' . $exception->getMessage());
    $loadError = 'Inventory is temporarily unavailable. Please try again shortly.';
}

$inventorySummary = [
    'requestable' => 0,
    'low' => 0,
    'unavailable' => 0,
];

foreach ($items as $summaryItem) {
    $summaryStatus = item_status($summaryItem);
    if (in_array($summaryStatus['class'], ['status-expired', 'status-out'], true)) {
        $inventorySummary['unavailable']++;
        continue;
    }

    $inventorySummary['requestable']++;
    if ($summaryStatus['class'] === 'status-low') {
        $inventorySummary['low']++;
    }
}

$pageTitle = 'Food inventory';
$pageId = 'home';
require APP_ROOT . '/includes/header.php';
?>

<section class="home-intro">
    <div class="container intro-layout">
        <div class="intro-copy">
            <p class="eyebrow">Community pantry availability</p>
            <h1>Know what is available before you plan a pickup.</h1>
            <p class="intro-lead">
                Check current stock, choose one item, and send a pickup request in a few clear steps.
            </p>
            <div class="hero-actions">
                <a class="button button-primary" href="#inventory">Browse available food</a>
                <a class="button button-secondary" href="<?= e(url('request.php')) ?>">Start a request <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ul class="assurance-list" aria-label="Service information">
                <li><strong>Live</strong> database stock</li>
                <li><strong>1 item</strong> per request</li>
                <li><strong>Future date</strong> pickup</li>
            </ul>
        </div>

        <aside class="availability-overview" aria-labelledby="availability-title">
            <div class="overview-heading">
                <div>
                    <p class="eyebrow">Today&apos;s overview</p>
                    <h2 id="availability-title">Pantry status</h2>
                </div>
                <span class="live-indicator"><span aria-hidden="true"></span> Live</span>
            </div>
            <div class="overview-grid">
                <div class="overview-stat">
                    <strong><?= $loadError === null ? $inventorySummary['requestable'] : '&mdash;' ?></strong>
                    <span>Requestable items</span>
                </div>
                <div class="overview-stat">
                    <strong><?= $loadError === null ? $inventorySummary['low'] : '&mdash;' ?></strong>
                    <span>Low-stock items</span>
                </div>
                <div class="overview-stat overview-stat-wide">
                    <strong><?= $loadError === null ? count($items) : '&mdash;' ?></strong>
                    <span>Total inventory records</span>
                </div>
            </div>
            <p class="overview-note">Availability is checked again when a request is submitted.</p>
        </aside>
    </div>
</section>

<section id="inventory" class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Choose an item</p>
                <h2>Current food inventory</h2>
            </div>
            <div class="status-legend" aria-label="Inventory status legend">
                <span><i class="legend-dot legend-available" aria-hidden="true"></i> Available</span>
                <span><i class="legend-dot legend-low" aria-hidden="true"></i> Low stock</span>
                <span><i class="legend-dot legend-unavailable" aria-hidden="true"></i> Unavailable</span>
            </div>
        </div>

        <?php if ($loadError !== null): ?>
            <div class="empty-state empty-state-error" role="alert">
                <h3>Unable to load inventory</h3>
                <p><?= e($loadError) ?></p>
            </div>
        <?php elseif ($items === []): ?>
            <div class="empty-state">
                <h3>No food items yet</h3>
                <p>The pantry administrator has not added any inventory.</p>
            </div>
        <?php else: ?>
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <?php $status = item_status($item); ?>
                    <article class="item-card <?= e($status['class']) ?>">
                        <div class="item-card-top">
                            <span class="status-badge <?= e($status['class']) ?>"><?= e($status['label']) ?></span>
                            <span class="quantity-block">
                                <strong class="quantity-value"><?= e($item['quantity']) ?></strong>
                                <small>units</small>
                            </span>
                        </div>
                        <h3><?= e($item['name']) ?></h3>
                        <dl class="item-details">
                            <div>
                                <dt>Expiry date</dt>
                                <dd><?= e(format_date($item['expiry_date'])) ?></dd>
                            </div>
                        </dl>

                        <?php if (!in_array($status['class'], ['status-expired', 'status-out'], true)): ?>
                            <a class="item-action" href="<?= e(url('request.php?item=' . $item['id'])) ?>">
                                Request <?= e($item['name']) ?> <span aria-hidden="true">&rarr;</span>
                            </a>
                        <?php else: ?>
                            <p class="item-unavailable">
                                <?= $status['class'] === 'status-expired' ? 'Not requestable because this item has expired.' : 'Not requestable until stock is added.' ?>
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-accent">
    <div class="container next-step-card">
        <div>
            <p class="eyebrow">Ready to continue?</p>
            <h2>Send your pickup request.</h2>
            <p>You will review the item, quantity, contact details and pickup date before submitting.</p>
        </div>
        <a class="button button-primary" href="<?= e(url('request.php')) ?>">Open request form <span aria-hidden="true">&rarr;</span></a>
    </div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
