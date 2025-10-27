<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/attendance_repository.php';

$user = require_login();

$today = date('Y-m-d');
$summary = attendance_summary_for_date($today);
$totalEmployees = count_employees();
$recentLogs = fetch_recent_attendance(6);

render_header('Dashboard', ['user' => $user]);
?>
<?php render_flash_messages(); ?>
<section class="dashboard">
    <div class="dashboard-grid">
        <article class="dashboard-card">
            <h3>Total employees</h3>
            <p class="metric"><?php echo h((string) $totalEmployees); ?></p>
            <p class="metric-subtitle">Enrolled with biometric credentials</p>
        </article>
        <article class="dashboard-card">
            <h3>Today&rsquo;s check-ins</h3>
            <p class="metric emphasis"><?php echo h((string) ($summary['check_in'] ?? 0)); ?></p>
            <p class="metric-subtitle">Recorded on <?php echo h(date('M j, Y')); ?></p>
        </article>
        <article class="dashboard-card">
            <h3>Today&rsquo;s check-outs</h3>
            <p class="metric emphasis"><?php echo h((string) ($summary['check_out'] ?? 0)); ?></p>
            <p class="metric-subtitle">Updated in real-time</p>
        </article>
        <article class="dashboard-card">
            <h3>Unique attendees</h3>
            <p class="metric"><?php echo h((string) ($summary['unique_employees'] ?? 0)); ?></p>
            <p class="metric-subtitle">Employees who interacted with the scanners today</p>
        </article>
    </div>

    <div class="card">
        <div class="card__header">
            <h3>Live attendance feed</h3>
            <a href="/attendance_logs.php" class="button-link">View detailed report</a>
        </div>
        <?php if (empty($recentLogs)): ?>
            <p class="empty-state">No biometric scans have been captured yet today.</p>
        <?php else: ?>
            <ul class="timeline">
                <?php foreach ($recentLogs as $log): ?>
                    <li class="timeline__item">
                        <div class="timeline__status timeline__status--<?php echo h($log['status']); ?>">
                            <?php echo h(format_status_label($log['status'])); ?>
                        </div>
                        <div class="timeline__details">
                            <h4><?php echo h($log['name']); ?></h4>
                            <p class="timeline__meta">
                                <?php echo h($log['department'] ?: 'Department unknown'); ?> &middot;
                                <?php echo h(format_datetime($log['captured_at'])); ?>
                            </p>
                            <p class="timeline__meta">
                                Device: <?php echo h($log['device_label']); ?><?php if ($log['location']): ?>
                                &middot; Location: <?php echo h($log['location']); ?><?php endif; ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<?php
render_footer();
