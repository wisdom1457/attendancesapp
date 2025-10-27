<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/attendance_repository.php';

$user = require_login();

$employeeCode = trim($_POST['employee_code'] ?? '');
$fingerprintSample = trim($_POST['fingerprint_sample'] ?? '');
$status = trim($_POST['status'] ?? 'check_in');
$deviceLabel = trim($_POST['device_label'] ?? 'Biometric Scanner');
$location = trim($_POST['location'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($employeeCode === '' || $fingerprintSample === '') {
        $error = 'Employee code and fingerprint sample are required.';
    } else {
        $employee = find_employee_by_code($employeeCode);
        if (!$employee) {
            $error = 'No enrolled employee matches that code.';
        } elseif (!verify_fingerprint($fingerprintSample, $employee['fingerprint_hash'])) {
            $error = 'Fingerprint verification failed. Please scan again.';
        } else {
            record_attendance((int) $employee['id'], $status === 'check_out' ? 'check_out' : 'check_in', $deviceLabel, $location !== '' ? $location : null, $notes !== '' ? $notes : null);
            set_flash('success', 'Attendance captured for ' . $employee['name'] . '.');
            redirect('/attendance_capture.php');
        }
    }
}

render_header('Capture attendance', ['user' => $user]);
?>
<?php render_flash_messages(); ?>
<section class="card">
    <div class="card__header">
        <div>
            <h2>Capture biometric attendance</h2>
            <p class="card__subtitle">Simulate a biometric scan using the employee&rsquo;s enrollment code and fingerprint phrase.</p>
        </div>
        <a href="/attendance_logs.php" class="button-link">View logs</a>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <div class="form-grid">
            <div class="form-group">
                <label for="employee_code">Employee code<span class="required">*</span></label>
                <input type="text" id="employee_code" name="employee_code" value="<?php echo h($employeeCode); ?>" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="status">Attendance type</label>
                <select id="status" name="status">
                    <option value="check_in" <?php echo $status === 'check_out' ? '' : 'selected'; ?>>Check-in</option>
                    <option value="check_out" <?php echo $status === 'check_out' ? 'selected' : ''; ?>>Check-out</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="fingerprint_sample">Fingerprint sample<span class="required">*</span></label>
            <input type="password" id="fingerprint_sample" name="fingerprint_sample" value="<?php echo h($fingerprintSample); ?>" required>
            <p class="hint">Ask the employee to present the same fingerprint phrase captured during enrollment.</p>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="device_label">Device label</label>
                <input type="text" id="device_label" name="device_label" value="<?php echo h($deviceLabel ?: 'Biometric Scanner'); ?>">
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?php echo h($location); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"><?php echo h($notes); ?></textarea>
        </div>

        <button type="submit" class="button primary-button">Record attendance</button>
    </form>
</section>
<?php
render_footer();
