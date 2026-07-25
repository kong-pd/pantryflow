<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$items = [];
$loadError = null;

try {
    $statement = db()->query(
        'SELECT id, name, quantity, expiry_date
         FROM food_items
         WHERE is_active = 1
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

<section class="hospitality-hero">
    <div class="container hero-frame">
        <div class="hero-visual" aria-hidden="true">
            <div class="hero-visual-top">
                <span>PANTRYFLOW</span>
                <span>COMMUNITY PANTRY SERVICE</span>
            </div>
            <div class="hero-emblem">
                <span>PantryFlow</span>
            </div>
            <div class="hero-visual-bottom">
                <span>Food support</span>
                <span>Thoughtfully coordinated</span>
            </div>
        </div>

        <div class="hero-editorial">
            <div>
                <p class="eyebrow">A considered pantry service</p>
                <h1>Support, prepared with care.</h1>
                <p class="intro-lead">
                    View what is available, arrange a suitable pickup, and receive a clear confirmation&mdash;all in one quiet journey.
                </p>
                <div class="hero-actions">
                    <a class="button button-primary" href="<?= e(url('request.php')) ?>">Arrange a pickup</a>
                    <a class="text-link" href="#inventory">Explore today&apos;s pantry <span aria-hidden="true">&darr;</span></a>
                </div>
            </div>

            <dl class="hero-ledger" aria-label="Today's pantry overview">
                <div>
                    <dt>Available today</dt>
                    <dd><?= $loadError === null ? $inventorySummary['requestable'] : '&mdash;' ?></dd>
                </div>
                <div>
                    <dt>Limited stock</dt>
                    <dd><?= $loadError === null ? $inventorySummary['low'] : '&mdash;' ?></dd>
                </div>
                <div>
                    <dt>Stock records</dt>
                    <dd><?= $loadError === null ? count($items) : '&mdash;' ?></dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<section class="service-journey" aria-labelledby="journey-title">
    <div class="container journey-inner">
        <div class="journey-heading">
            <p class="eyebrow">Your pickup journey</p>
            <h2 id="journey-title">Simple from selection to confirmation.</h2>
        </div>
        <ol>
            <li><span>01</span><div><strong>Browse</strong><p>Review live availability.</p></div></li>
            <li><span>02</span><div><strong>Arrange</strong><p>Choose item, quantity and date.</p></div></li>
            <li><span>03</span><div><strong>Confirm</strong><p>Stock is checked and reserved.</p></div></li>
        </ol>
    </div>
</section>

<section id="inventory" class="section">
    <div class="container">
        <div class="section-heading inventory-heading">
            <div>
                <p class="eyebrow">Today&apos;s pantry</p>
                <h2>Available provisions</h2>
                <p class="section-intro">Each request is for one item. Availability is verified again when you confirm.</p>
            </div>
            <p class="inventory-note">
                <?php if ($loadError === null): ?>
                    <?= e($inventorySummary['requestable']) ?> of <?= e(count($items)) ?> items are ready to request today.
                <?php else: ?>
                    Live availability is currently unavailable.
                <?php endif; ?>
            </p>
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
            <div class="inventory-list">
                <?php foreach ($items as $index => $item): ?>
                    <?php $status = item_status($item); ?>
                    <article class="inventory-row <?= e($status['class']) ?>">
                        <div class="item-ordinal" aria-hidden="true"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></div>
                        <div class="item-description">
                            <span class="status-label <?= e($status['class']) ?>"><?= e($status['label']) ?></span>
                            <h3><?= e($item['name']) ?></h3>
                            <p>Best before <?= e(format_date($item['expiry_date'])) ?></p>
                        </div>
                        <div class="item-availability">
                            <strong><?= e($item['quantity']) ?></strong>
                            <span>unit<?= (int) $item['quantity'] === 1 ? '' : 's' ?> remaining</span>
                        </div>

                        <?php if (!in_array($status['class'], ['status-expired', 'status-out'], true)): ?>
                            <a class="item-action" aria-label="Select <?= e($item['name']) ?> for pickup" href="<?= e(url('request.php?item=' . $item['id'])) ?>">
                                Select <span aria-hidden="true">&rarr;</span>
                            </a>
                        <?php else: ?>
                            <span class="item-unavailable">Not currently requestable</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="closing-invitation">
    <div class="container invitation-inner">
        <div>
            <p class="eyebrow">Pantry assistance</p>
            <h2>Ready when you are.</h2>
            <p>Choose your item and pickup details. The pantry will take care of the stock check.</p>
        </div>
        <a class="button button-light" href="<?= e(url('request.php')) ?>">Arrange a pickup <span aria-hidden="true">&rarr;</span></a>
    </div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
