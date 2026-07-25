<?php

declare(strict_types=1);
?>
</main>

<footer id="site-footer" class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <strong>PantryFlow</strong>
            <p>Community food support, thoughtfully coordinated.</p>
        </div>
        <nav class="footer-nav" aria-label="Footer navigation">
            <a href="<?= e(url('index.php#inventory')) ?>">Availability</a>
            <a href="<?= e(url('request.php')) ?>">Arrange a pickup</a>
            <?php if (recent_request_ids() !== []): ?>
                <a href="<?= e(url('history.php')) ?>">My pickups</a>
            <?php endif; ?>
            <a href="<?= e(url(is_admin() ? 'dashboard.php' : 'login.php')) ?>"><?= is_admin() ? 'Operations' : 'Team access' ?></a>
        </nav>
        <p class="footer-note">&copy; <?= e(date('Y')) ?> PantryFlow<br><span>Community Pantry Service</span></p>
    </div>
</footer>
<?php if ($pageId === 'dashboard'): ?>
    <script src="<?= e(url('assets/js/admin-actions.js')) ?>" defer></script>
<?php endif; ?>
</body>
</html>
