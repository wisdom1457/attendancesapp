<?php

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/service_repository.php';

$user = require_login();
require_role($user, ['admin']);

$flashMessage = null;
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';

    if ($serviceId <= 0 || $price === '') {
        $flashMessage = 'Please provide a valid price.';
        $flashType = 'error';
    } else {
        $normalizedPrice = filter_var($price, FILTER_VALIDATE_FLOAT);

        if ($normalizedPrice === false || $normalizedPrice < 0) {
            $flashMessage = 'Prices must be positive numbers.';
            $flashType = 'error';
        } else {
            update_service_price($serviceId, round($normalizedPrice, 2));
            $flashMessage = 'Service pricing updated successfully.';
            $flashType = 'success';
        }
    }
}

$services = fetch_services();
$totalExpenses = fetch_total_expenses();
$recentExpenses = fetch_recent_expenses();

render_header('Admin Dashboard', $user);

render_flash_message($flashMessage, $flashType);
?>

<section class="card">
    <h2>Administrative Overview</h2>
    <p>Monitor hostel spending and manage service pricing from this dashboard.</p>
</section>

<section class="dashboard-grid admin-grid">
    <article class="dashboard-card emphasis">
        <h3>Total Recorded Expenses</h3>
        <p class="total-expenses">₹<?php echo number_format($totalExpenses, 2); ?></p>
        <p class="hint">The total reflects all entries logged in the expenses register.</p>
    </article>

    <article class="dashboard-card">
        <h3>Recent Expenses</h3>
        <?php if (empty($recentExpenses)): ?>
            <p>No expenses recorded yet.</p>
        <?php else: ?>
            <ul class="expense-list">
                <?php foreach ($recentExpenses as $expense): ?>
                    <li>
                        <span class="expense-service"><?php echo htmlspecialchars($expense['service_name']); ?></span>
                        <span class="expense-amount">₹<?php echo number_format((float) $expense['amount'], 2); ?></span>
                        <span class="expense-date"><?php echo htmlspecialchars(date('d M Y', strtotime($expense['expense_date']))); ?></span>
                        <?php if (!empty($expense['description'])): ?>
                            <span class="expense-description"><?php echo htmlspecialchars($expense['description']); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>

    <article class="dashboard-card full-width">
        <h3>Service Pricing</h3>
        <p>Update the standard cost assigned to each service.</p>
        <div class="table-wrapper">
            <table class="service-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Current Price (₹)</th>
                        <th>Last Updated</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td><?php echo number_format((float) $service['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($service['updated_at']))); ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">
                                    <label class="sr-only" for="price-<?php echo (int) $service['id']; ?>">Price for <?php echo htmlspecialchars($service['name']); ?></label>
                                    <input type="number" step="0.01" min="0" name="price" id="price-<?php echo (int) $service['id']; ?>" value="<?php echo htmlspecialchars(number_format((float) $service['price'], 2, '.', '')); ?>" required>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php
render_footer();
?>
