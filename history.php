<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$requestIds = recent_request_ids();
$recentRequests = [];
$loadError = null;

if ($requestIds !== []) {
    try {
        $placeholders = implode(',', array_fill(0, count($requestIds), '?'));
        $historyStatement = db()->prepare(
            'SELECT cr.id, cr.pickup_date, cr.requested_qty, cr.status, cr.created_at, fi.name AS item_name
             FROM client_requests AS cr
             INNER JOIN food_items AS fi ON fi.id = cr.food_item_id
             WHERE cr.id IN (' . $placeholders . ')'
        );
        $historyStatement->execute($requestIds);
        $recordsById = [];

        foreach ($historyStatement->fetchAll() as $record) {
            $recordsById[(int) $record['id']] = $record;
        }

        foreach ($requestIds as $requestId) {
            if (isset($recordsById[$requestId])) {
                $recentRequests[] = $recordsById[$requestId];
            } else {
                forget_recent_request($requestId);
            }
        }
    } catch (Throwable $exception) {
        error_log('Recent pickup load failed: ' . $exception->getMessage());
        $loadError = 'Recent pickups are temporarily unavailable.';
    }
}

$pageTitle = 'My pickups';
$pageId = 'history';
require APP_ROOT . '/includes/header.php';
?>

<section class="history-section" aria-labelledby="history-title">
    <div class="container history-shell">
        <header class="history-heading">
            <div>
                <p class="eyebrow">This browser session</p>
                <h1 id="history-title">Recent pickups.</h1>
                <p>Your recent requests stay here while this browser session remains open.</p>
            </div>
            <a class="button button-primary" href="<?= e(url('request.php')) ?>">Arrange another pickup</a>
        </header>

        <?php if ($loadError !== null): ?>
            <div class="history-empty" role="alert">
                <p class="eyebrow">Unable to load</p>
                <h2>Please try again shortly.</h2>
                <p><?= e($loadError) ?></p>
            </div>
        <?php elseif ($recentRequests === []): ?>
            <div class="history-empty">
                <p class="eyebrow">No recent pickups</p>
                <h2>Your next confirmation will appear here.</h2>
                <p>No account is required, and no public request directory is exposed.</p>
            </div>
        <?php else: ?>
            <ol class="pickup-history" aria-label="Recent pickup requests">
                <?php foreach ($recentRequests as $request): ?>
                    <?php $pickupDate = parse_ymd_date((string) $request['pickup_date']); ?>
                    <li>
                        <span class="pickup-reference">PF-<?= e(str_pad((string) $request['id'], 4, '0', STR_PAD_LEFT)) ?></span>
                        <div class="pickup-name">
                            <strong><?= e($request['item_name']) ?></strong>
                            <small><?= e($request['requested_qty']) ?> unit<?= (int) $request['requested_qty'] === 1 ? '' : 's' ?> · <?= $request['status'] === 'rejected' ? 'Rejected' : 'Pending' ?></small>
                        </div>
                        <div class="pickup-date">
                            <span>Pickup</span>
                            <strong><?= e($pickupDate?->format('d M Y') ?? 'Date unavailable') ?></strong>
                        </div>
                        <a class="text-link" href="<?= e(url('request.php?confirmed=' . $request['id'])) ?>">View confirmation <span aria-hidden="true">&rarr;</span></a>
                    </li>
                <?php endforeach; ?>
            </ol>
            <p class="history-privacy">Only request references created in this browser session are shown. Contact details remain in the protected pantry dashboard.</p>
        <?php endif; ?>
    </div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
