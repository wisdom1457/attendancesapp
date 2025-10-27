<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/attendance_repository.php';

$user = require_login();
$employees = fetch_employees();

render_header('Employees', ['user' => $user]);
?>
<?php render_flash_messages(); ?>
<section class="card">
    <div class="card__header">
        <div>
            <h2>Employee directory</h2>
            <p class="card__subtitle">Manage biometric enrollments and review enrollment metadata.</p>
        </div>
        <a href="/employee_create.php" class="button primary-button">Enroll employee</a>
    </div>

    <?php if (empty($employees)): ?>
        <p class="empty-state">No employees have been enrolled yet. Start by adding your first team member.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Employee code</th>
                        <th>Fingerprint hint</th>
                        <th>Enrolled on</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td data-label="Name"><?php echo h($employee['name']); ?></td>
                            <td data-label="Email"><?php echo h($employee['email']); ?></td>
                            <td data-label="Department"><?php echo h($employee['department'] ?: '—'); ?></td>
                            <td data-label="Role"><?php echo h($employee['job_title'] ?: '—'); ?></td>
                            <td data-label="Employee code"><code><?php echo h($employee['employee_code']); ?></code></td>
                            <td data-label="Fingerprint hint"><span class="badge"><?php echo h($employee['fingerprint_hint']); ?></span></td>
                            <td data-label="Enrolled on"><?php echo h(format_datetime($employee['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
