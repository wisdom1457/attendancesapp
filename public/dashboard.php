<?php
require_once __DIR__ . '/../src/helpers.php';

$user = require_login();

render_header('Dashboard', $user);
?>
<section class="card">
    <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
    <p>Your role is <strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role']))); ?></strong>. Use the shortcuts below to get started.</p>
</section>

<section class="dashboard-grid">
    <article class="dashboard-card">
        <h3>Common Actions</h3>
        <ul>
            <li><a href="#">Record a delivery</a></li>
            <li><a href="#">Review pending approvals</a></li>
            <li><a href="#">View transactions</a></li>
        </ul>
        <p class="hint">These features will become available in later phases.</p>
    </article>

    <article class="dashboard-card">
        <h3>Role Guidance</h3>
        <?php if ($user['role'] === 'delivery_person'): ?>
            <p>Prepare to log deliveries for eggs, chicken, vegetables, and more. Approved entries will move to the transaction log.</p>
        <?php elseif ($user['role'] === 'checker'): ?>
            <p>Monitor incoming deliveries. You'll soon be able to approve or reject pending records to keep the inventory accurate.</p>
        <?php else: ?>
            <p>As an administrator you will have full visibility into all hostel supply transactions and settings.</p>
        <?php endif; ?>
    </article>

    <article class="dashboard-card">
        <h3>Next steps</h3>
        <ul>
            <li>Phase 2 will introduce product catalog management.</li>
            <li>Phase 3 will activate the delivery approval workflow.</li>
            <li>Phase 4 adds reporting, printing, and analytics.</li>
        </ul>
    </article>
</section>
<?php
render_footer();
