<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$requests = [];
$lowStockItems = [];
$addItemOld = consume_form('add_item_old');
$loadError = null;

try {
    $requestStatement = db()->query(
        'SELECT
            cr.id,
            cr.client_name,
            cr.contact,
            cr.pickup_date,
            cr.requested_qty,
            cr.created_at,
            fi.name AS food_item_name
         FROM client_requests AS cr
         INNER JOIN food_items AS fi ON fi.id = cr.food_item_id
         ORDER BY cr.created_at DESC'
    );
    $requests = $requestStatement->fetchAll();

    $lowStockStatement = db()->query(
        'SELECT id, name, quantity, expiry_date
         FROM food_items
         WHERE quantity < 5
         ORDER BY quantity ASC, name ASC'
    );
    $lowStockItems = $lowStockStatement->fetchAll();
} catch (Throwable $exception) {
    error_log('Dashboard load failed: ' . $exception->getMessage());
    $loadError = 'Dashboard data is temporarily unavailable.';
}

$requestedUnits = array_sum(array_map(
    static fn (array $request): int => (int) $request['requested_qty'],
    $requests
));

$pageTitle = 'Administrator dashboard';
$pageId = 'dashboard';
require APP_ROOT . '/includes/header.php';
?>

<section class="page-heading dashboard-heading">
    <div class="container dashboard-title-row">
        <div>
            <p class="eyebrow">Pantry operations</p>
            <h1>Requests &amp; stock, in one place.</h1>
            <p>Start with upcoming pickups, then handle any inventory warning.</p>
        </div>
        <div class="dashboard-heading-actions">
            <span class="admin-badge"><span aria-hidden="true"></span> <?= e($_SESSION['admin_username']) ?></span>
            <a class="button button-secondary" href="#add-item">Add food item</a>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <?php if ($loadError !== null): ?>
            <div class="empty-state empty-state-error" role="alert">
                <h2>Unable to load dashboard</h2>
                <p><?= e($loadError) ?></p>
            </div>
        <?php else: ?>
            <div class="stats-grid" aria-label="Dashboard summary">
                <article class="stat-card">
                    <span>Pickup requests</span>
                    <strong><?= count($requests) ?></strong>
                    <small>Saved in the database</small>
                </article>
                <article class="stat-card">
                    <span>Units requested</span>
                    <strong><?= $requestedUnits ?></strong>
                    <small>Across all pickups</small>
                </article>
                <article class="stat-card stat-warning">
                    <span>Low-stock items</span>
                    <strong><?= count($lowStockItems) ?></strong>
                    <small>Fewer than 5 units</small>
                </article>
            </div>

            <div class="dashboard-grid">
                <section class="panel panel-wide" aria-labelledby="requests-title">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">Pickup queue</p>
                            <h2 id="requests-title">Client requests</h2>
                        </div>
                        <span class="count-badge"><?= count($requests) ?> total</span>
                    </div>

                    <?php if ($requests === []): ?>
                        <div class="empty-state compact">
                            <h3>No requests submitted</h3>
                            <p>New requests will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table>
                                <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Contact</th>
                                    <th>Item</th>
                                    <th class="numeric">Qty</th>
                                    <th>Pickup</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?= e($request['client_name']) ?></td>
                                        <td><a class="contact-link" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $request['contact'])) ?>"><?= e($request['contact']) ?></a></td>
                                        <td><?= e($request['food_item_name']) ?></td>
                                        <td class="numeric"><?= e($request['requested_qty']) ?></td>
                                        <td><span class="date-chip"><?= e(format_date($request['pickup_date'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="panel" aria-labelledby="low-stock-title">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">Needs attention</p>
                            <h2 id="low-stock-title">Low stock</h2>
                        </div>
                        <span class="count-badge count-warning"><?= count($lowStockItems) ?></span>
                    </div>

                    <?php if ($lowStockItems === []): ?>
                        <div class="empty-state compact">
                            <h3>Stock levels are healthy</h3>
                            <p>No item has fewer than 5 units.</p>
                        </div>
                    <?php else: ?>
                        <ul class="stock-list">
                            <?php foreach ($lowStockItems as $item): ?>
                                <li>
                                    <div>
                                        <strong><?= e($item['name']) ?></strong>
                                        <span>Expires <?= e(format_date($item['expiry_date'])) ?></span>
                                    </div>
                                    <span class="stock-quantity"><?= e($item['quantity']) ?> unit<?= (int) $item['quantity'] === 1 ? '' : 's' ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <section id="add-item" class="panel" aria-labelledby="add-item-title">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">Inventory action</p>
                            <h2 id="add-item-title">Add a food item</h2>
                        </div>
                    </div>

                    <form action="<?= e(url('add-item.php')) ?>" method="post">
                        <div class="field">
                            <label for="item_name">Item name</label>
                            <input id="item_name" name="name" type="text" maxlength="100" autocomplete="off" required value="<?= e($addItemOld['name'] ?? '') ?>">
                        </div>
                        <div class="field-grid compact-grid">
                            <div class="field">
                                <label for="item_quantity">Quantity</label>
                                <input id="item_quantity" name="quantity" type="number" min="0" step="1" inputmode="numeric" value="<?= e($addItemOld['quantity'] ?? '0') ?>" required>
                            </div>
                            <div class="field">
                                <label for="item_expiry">Expiry date <span>(optional)</span></label>
                                <input id="item_expiry" name="expiry_date" type="date" value="<?= e($addItemOld['expiry_date'] ?? '') ?>">
                            </div>
                        </div>
                        <p class="form-note">Use 0 quantity for a known item that is currently unavailable.</p>
                        <button class="button button-primary button-block" type="submit">Add to inventory</button>
                    </form>
                </section>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
