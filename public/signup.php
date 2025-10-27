<?php
require_once __DIR__ . '/../src/helpers.php';

start_session();

if (current_user()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
$name = '';
$email = '';
$role = 'delivery_person';
$roles = [
    'delivery_person' => 'Delivery Person',
    'sanitary_seller' => 'Sanitary Accessories Seller',
    'ac_servicer' => 'AC Servicer',
    'checker' => 'Checker / Approver',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'delivery_person';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!array_key_exists($role, $roles)) {
        $error = 'Please choose a valid role.';
    } else {
        try {
            $normalizedEmail = strtolower($email);
            register_user($name, $normalizedEmail, $password, $role);
            attempt_login($normalizedEmail, $password);
            header('Location: /dashboard.php');
            exit;
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000 || strpos(strtolower($e->getMessage()), 'unique') !== false) {
                $error = 'An account with that email already exists.';
            } else {
                $error = 'Unable to create account. Please try again.';
            }
        }
    }
}

render_header('Sign up');
?>
<div class="card">
    <h2>Create an account</h2>
    <?php render_flash_message($error); ?>
    <form method="post">
        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <?php foreach ($roles as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($role) && $role === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit">Create account</button>
    </form>
    <p class="hint">Already have an account? <a href="/index.php">Sign in</a>.</p>
</div>
<?php
render_footer();
