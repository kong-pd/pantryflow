<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$requests = [];
$lowStockItems = [];
$inventoryItems = [];
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
            cr.status,
            cr.reviewed_at,
            cr.created_at,
            fi.name AS food_item_name
         FROM client_requests AS cr
         INNER JOIN food_items AS fi ON fi.id = cr.food_item_id
         ORDER BY cr.status ASC, cr.pickup_date ASC, cr.created_at DESC'
    );
    $requests = $requestStatement->fetchAll();

    $lowStockStatement = db()->query(
        'SELECT id, name, quantity, expiry_date
         FROM food_items
         WHERE quantity < 5
           AND is_active = 1
         ORDER BY quantity ASC, name ASC'
    );
    $lowStockItems = $lowStockStatement->fetchAll();

    $inventoryStatement = db()->query(
        'SELECT
            fi.id,
            fi.name,
            fi.quantity,
            fi.expiry_date,
            fi.is_active,
            fi.archived_at,
            fi.created_at,
            COUNT(cr.id) AS request_count
         FROM food_items AS fi
         LEFT JOIN client_requests AS cr ON cr.food_item_id = fi.id
         GROUP BY fi.id, fi.name, fi.quantity, fi.expiry_date, fi.is_active, fi.archived_at, fi.created_at
         ORDER BY fi.is_active DESC, fi.name ASC'
    );
    $inventoryItems = $inventoryStatement->fetchAll();
} catch (Throwable $exception) {
    error_log('Dashboard load failed: ' . $exception->getMessage());
    $loadError = 'Dashboard data is temporarily unavailable.';
}

$pendingRequests = array_values(array_filter(
    $requests,
    static fn (array $request): bool => $request['status'] === 'pending'
));
$requestedUnits = array_sum(array_map(
    static fn (array $request): int => (int) $request['requested_qty'],
    $pendingRequests
));
$activeInventoryCount = count(array_filter(
    $inventoryItems,
    static fn (array $item): bool => (int) $item['is_active'] === 1
));

$pageTitle = 'Administrator dashboard';
$pageId = 'dashboard';
require APP_ROOT . '/includes/header.php';
?>

<section class="dashboard-overview" aria-labelledby="dashboard-title">
    <div class="container">
        <div class="dashboard-topline">
            <p class="eyebrow">Pantry operations</p>
        </div>
        <div class="dashboard-intro">
            <div>
                <h1 id="dashboard-title">Operations desk.</h1>
                <p>Pickup coordination and inventory controls.</p>
            </div>
            <div class="dashboard-actions">
                <a class="text-link" href="#inventory">View inventory <span aria-hidden="true">&darr;</span></a>
                <a class="button button-secondary" href="#add-item">Add item</a>
            </div>
        </div>

        <?php if ($loadError === null): ?>
            <dl class="operations-summary" aria-label="Operations summary">
                <div>
                    <dt>Pickup requests</dt>
                    <dd><?= count($pendingRequests) ?></dd>
                    <small>Pending review</small>
                </div>
                <div>
                    <dt>Units requested</dt>
                    <dd><?= $requestedUnits ?></dd>
                    <small>Across all pickups</small>
                </div>
                <div class="summary-warning">
                    <dt>Low-stock items</dt>
                    <dd><?= count($lowStockItems) ?></dd>
                    <small>Below 5 units</small>
                </div>
                <div>
                    <dt>Inventory items</dt>
                    <dd><?= $activeInventoryCount ?></dd>
                    <small><?= count($inventoryItems) ?> records total</small>
                </div>
            </dl>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-content">
    <div class="container">
        <?php if ($loadError !== null): ?>
            <div class="empty-state empty-state-error" role="alert">
                <h2>Unable to load dashboard</h2>
                <p><?= e($loadError) ?></p>
            </div>
        <?php else: ?>
            <div class="dashboard-workspace">
                <section class="operations-panel operations-panel-primary" aria-labelledby="requests-title">
                    <header class="operations-panel-heading">
                        <div>
                            <p class="eyebrow">Pickup queue</p>
                            <h2 id="requests-title">Upcoming requests</h2>
                        </div>
                            <span class="panel-count"><?= count($pendingRequests) ?> pending · <?= count($requests) ?> total</span>
                    </header>

                    <?php if ($requests === []): ?>
                        <div class="dashboard-empty">
                            <h3>No pickup requests.</h3>
                            <p>New confirmed arrangements will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="requests-table">
                                <thead>
                                <tr>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Client</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Provision</th>
                                    <th scope="col" class="numeric">Qty</th>
                                    <th scope="col">Pickup</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"><span class="sr-only">Action</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr class="<?= $request['status'] === 'rejected' ? 'record-muted' : '' ?>">
                                        <td><span class="request-reference">PF-<?= e(str_pad((string) $request['id'], 4, '0', STR_PAD_LEFT)) ?></span></td>
                                        <td><?= e($request['client_name']) ?></td>
                                        <td><a class="contact-link" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $request['contact'])) ?>"><?= e($request['contact']) ?></a></td>
                                        <td><?= e($request['food_item_name']) ?></td>
                                        <td class="numeric"><?= e($request['requested_qty']) ?></td>
                                        <td><span class="date-chip"><?= e(format_date($request['pickup_date'])) ?></span></td>
                                        <td><span class="status-label status-<?= e($request['status']) ?>"><?= e(ucfirst((string) $request['status'])) ?></span></td>
                                        <td class="table-action-cell">
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <form action="<?= e(url('reject-request.php')) ?>" method="post" data-confirm="Reject this pickup request? The reserved units will return to inventory.">
                                                    <input type="hidden" name="request_id" value="<?= e($request['id']) ?>">
                                                    <button class="table-action table-action-danger" type="submit">Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="table-action-complete">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <div class="dashboard-secondary">
                    <section class="operations-panel" aria-labelledby="low-stock-title">
                        <header class="operations-panel-heading">
                            <div>
                                <p class="eyebrow">Inventory watch</p>
                                <h2 id="low-stock-title">Low stock</h2>
                            </div>
                            <span class="panel-count panel-count-warning"><?= count($lowStockItems) ?> item<?= count($lowStockItems) === 1 ? '' : 's' ?></span>
                        </header>

                        <?php if ($lowStockItems === []): ?>
                            <div class="dashboard-empty dashboard-empty-compact">
                                <h3>Stock levels are healthy.</h3>
                                <p>No item has fewer than 5 units.</p>
                            </div>
                        <?php else: ?>
                            <ul class="stock-list">
                                <?php foreach ($lowStockItems as $item): ?>
                                    <li>
                                        <div>
                                            <strong><?= e($item['name']) ?></strong>
                                            <span>Expiry <?= e(format_date($item['expiry_date'])) ?></span>
                                        </div>
                                        <span class="stock-quantity"><?= e($item['quantity']) ?> unit<?= (int) $item['quantity'] === 1 ? '' : 's' ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>

                    <section id="add-item" class="operations-panel" aria-labelledby="add-item-title">
                        <header class="operations-panel-heading">
                            <div>
                                <p class="eyebrow">Inventory entry</p>
                                <h2 id="add-item-title">Add an item</h2>
                            </div>
                        </header>

                        <form class="inventory-entry-form" action="<?= e(url('add-item.php')) ?>" method="post">
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
                            <div class="inventory-entry-action">
                                <p class="form-note">Use 0 for an item that is not currently available.</p>
                                <button class="button button-primary" type="submit">Add to inventory</button>
                            </div>
                        </form>
                    </section>
                </div>

                <section id="inventory" class="operations-panel inventory-ledger" aria-labelledby="inventory-title">
                    <header class="operations-panel-heading">
                        <div>
                            <p class="eyebrow">Complete inventory</p>
                            <h2 id="inventory-title">All food items</h2>
                        </div>
                        <span class="panel-count"><?= $activeInventoryCount ?> active · <?= count($inventoryItems) ?> total</span>
                    </header>

                    <?php if ($inventoryItems === []): ?>
                        <div class="dashboard-empty dashboard-empty-compact">
                            <h3>No inventory items.</h3>
                            <p>Add the first item using the inventory entry form.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="requests-table inventory-table">
                                <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="numeric">Quantity</th>
                                    <th scope="col">Expiry</th>
                                    <th scope="col" class="numeric">Requests</th>
                                    <th scope="col"><span class="sr-only">Actions</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($inventoryItems as $item): ?>
                                    <?php
                                    $isActive = (int) $item['is_active'] === 1;
                                    $status = $isActive
                                        ? item_status($item)
                                        : ['label' => 'Archived', 'class' => 'status-archived'];
                                    ?>
                                    <tr class="<?= $isActive ? '' : 'record-muted' ?>">
                                        <td><?= e($item['name']) ?></td>
                                        <td><span class="status-label <?= e($status['class']) ?>"><?= e($status['label']) ?></span></td>
                                        <td class="numeric"><?= e($item['quantity']) ?></td>
                                        <td><span class="date-chip"><?= e(format_date($item['expiry_date'])) ?></span></td>
                                        <td class="numeric"><?= e($item['request_count']) ?></td>
                                        <td class="table-action-cell">
                                            <div class="table-actions">
                                                <?php if ($isActive): ?>
                                                    <form action="<?= e(url('inventory-action.php')) ?>" method="post" data-confirm="Archive this item? It will disappear from public availability but stay in the inventory record.">
                                                        <input type="hidden" name="item_id" value="<?= e($item['id']) ?>">
                                                        <input type="hidden" name="action" value="archive">
                                                        <button class="table-action" type="submit">Archive</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="<?= e(url('inventory-action.php')) ?>" method="post">
                                                        <input type="hidden" name="item_id" value="<?= e($item['id']) ?>">
                                                        <input type="hidden" name="action" value="restore">
                                                        <button class="table-action" type="submit">Restore</button>
                                                    </form>
                                                    <?php if ((int) $item['request_count'] === 0): ?>
                                                        <form action="<?= e(url('inventory-action.php')) ?>" method="post" data-confirm="Delete this unreferenced item permanently? This cannot be undone.">
                                                            <input type="hidden" name="item_id" value="<?= e($item['id']) ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button class="table-action table-action-danger" type="submit">Delete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
