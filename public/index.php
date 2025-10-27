<?php
require_once __DIR__ . '/../src/helpers.php';

start_session();

if (current_user()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attempt_login($emailValue, $password)) {
        header('Location: /dashboard.php');
        exit;
    }

    $error = 'Invalid email or password.';
}

render_header('Login');
?>
<div class="card">
    <h2>Sign in</h2>
    <?php render_flash_message($error); ?>
    <form method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($emailValue); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Sign in</button>
    </form>
    <p class="hint">Use one of the seeded accounts after running the setup script or <a href="/signup.php">create a new account</a>.</p>
</div>
<?php
render_footer();
