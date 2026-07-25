<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_admin()) {
    redirect('dashboard.php');
}

if (is_post()) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (
        hash_equals(ADMIN_USERNAME, $username)
        && hash_equals(ADMIN_PASSWORD, $password)
    ) {
        session_regenerate_id(true);
        $_SESSION['admin_username'] = ADMIN_USERNAME;
        set_flash('success', 'Logged in successfully.');
        redirect('dashboard.php');
    }

    set_flash('error', 'Incorrect username or password.');
    redirect('login.php');
}

$pageTitle = 'Administrator login';
$pageId = 'login';
require APP_ROOT . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container auth-layout">
        <div class="auth-copy">
            <a class="back-link" href="<?= e(url('index.php')) ?>"><span aria-hidden="true">&larr;</span> Back to inventory</a>
            <p class="eyebrow">Pantry team only</p>
            <h1>Manage requests &amp; stock.</h1>
            <p>Log in to review pickup details, see low-stock warnings, and add food items.</p>
            <ul class="auth-feature-list">
                <li><span aria-hidden="true">01</span> Review every client request</li>
                <li><span aria-hidden="true">02</span> Find stock below 5 units</li>
                <li><span aria-hidden="true">03</span> Add inventory safely</li>
            </ul>
        </div>

        <div class="auth-card">
            <div class="auth-card-heading">
                <span class="auth-lock" aria-hidden="true">PF</span>
                <div>
                    <p class="eyebrow">Protected session</p>
                    <h2>Administrator login</h2>
                </div>
            </div>
            <form action="<?= e(url('login.php')) ?>" method="post" aria-describedby="login-note">
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>

                <p id="login-note" class="form-note">Credentials are case-sensitive.</p>
                <button class="button button-primary button-block" type="submit">Open dashboard <span aria-hidden="true">&rarr;</span></button>
            </form>
            <p class="auth-help">Direct dashboard access stays blocked until this login succeeds.</p>
        </div>
    </div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
