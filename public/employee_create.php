<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/attendance_repository.php';

$user = require_login();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');
$jobTitle = trim($_POST['job_title'] ?? '');
$fingerprintPhrase = trim($_POST['fingerprint_phrase'] ?? '');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        create_employee($name, $email, $department, $jobTitle, $fingerprintPhrase);
        set_flash('success', 'Employee enrolled successfully. Share the employee code and have them scan the same fingerprint phrase.');
        redirect('/employees.php');
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (Throwable $exception) {
        $error = 'Unable to enroll the employee right now. Please try again.';
    }
}

render_header('Enroll employee', ['user' => $user]);
?>
<section class="card">
    <div class="card__header">
        <div>
            <h2>Enroll a new employee</h2>
            <p class="card__subtitle">Capture the employee&rsquo;s details and biometric fingerprint phrase.</p>
        </div>
        <a href="/employees.php" class="button-link">Back to directory</a>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Full name<span class="required">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo h($name); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email address<span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo h($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" value="<?php echo h($department); ?>">
            </div>
            <div class="form-group">
                <label for="job_title">Role / title</label>
                <input type="text" id="job_title" name="job_title" value="<?php echo h($jobTitle); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="fingerprint_phrase">Fingerprint phrase<span class="required">*</span></label>
            <input type="password" id="fingerprint_phrase" name="fingerprint_phrase" value="<?php echo h($fingerprintPhrase); ?>" required>
            <p class="hint">This passphrase represents the employee&rsquo;s fingerprint. Ask the employee to place the same fingerprint phrase when checking in or out.</p>
        </div>
        <button type="submit" class="button primary-button">Enroll employee</button>
    </form>
</section>
<?php
render_footer();
