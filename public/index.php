<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

start_session();

if (current_user()) {
    redirect('/dashboard.php');
}

$emailValue = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attempt_login($emailValue, $password)) {
        set_flash('success', 'Welcome back!');
        redirect('/dashboard.php');
    } else {
        $error = 'We could not match that email and password. Please try again.';
    }
}

render_header('Sign in', ['show_nav' => false]);
?>
<div class="auth-wrapper">
    <div class="card auth-card">
        <h2 class="card__title">Biometric Attendance Portal</h2>
        <p class="card__subtitle">Sign in to manage enrollments and real-time attendance.</p>
        <?php render_flash_messages(); ?>
        <?php if ($error): ?>
            <div class="flash flash-error"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post" class="form">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?php echo h($emailValue); ?>" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="button primary-button">Sign in</button>
        </form>
        <p class="hint">Use the seeded administrator account (<code>admin@attendance.local</code> / <code>admin123</code>) after running <code>php scripts/setup.php</code>.</p>
    </div>
</div>
<?php
render_footer();
