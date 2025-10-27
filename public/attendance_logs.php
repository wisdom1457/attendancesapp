<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/attendance_repository.php';

$user = require_login();

$dateFilter = trim($_GET['date'] ?? '');
if ($dateFilter !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $dateFilter = '';
}

$logs = fetch_attendance_logs($dateFilter !== '' ? $dateFilter : null, 100);

render_header('Attendance logs', ['user' => $user]);
?>
<section class="card">
    <div class="card__header">
        <div>
            <h2>Attendance logs</h2>
            <p class="card__subtitle">Review biometric scans from your enrollment devices.</p>
        </div>
        <a href="/attendance_capture.php" class="button-link">Capture new attendance</a>
    </div>

    <form method="get" class="filter-form">
        <div class="form-group">
            <label for="date">Filter by date</label>
            <input type="date" id="date" name="date" value="<?php echo h($dateFilter); ?>">
        </div>
        <button type="submit" class="button">Apply</button>
        <?php if ($dateFilter !== ''): ?>
            <a href="/attendance_logs.php" class="button-link">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($logs)): ?>
        <p class="empty-state">No attendance logs were found for the selected filters.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Device</th>
                        <th>Location</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td data-label="Time"><?php echo h(format_datetime($log['captured_at'])); ?></td>
                            <td data-label="Employee">
                                <strong><?php echo h($log['name']); ?></strong><br>
                                <small>Code: <code><?php echo h($log['employee_code']); ?></code></small>
                            </td>
                            <td data-label="Department"><?php echo h($log['department'] ?: '—'); ?></td>
                            <td data-label="Status"><span class="badge badge--<?php echo h($log['status']); ?>"><?php echo h(format_status_label($log['status'])); ?></span></td>
                            <td data-label="Device"><?php echo h($log['device_label']); ?></td>
                            <td data-label="Location"><?php echo h($log['location'] ?: '—'); ?></td>
                            <td data-label="Notes"><?php echo h($log['notes'] ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
