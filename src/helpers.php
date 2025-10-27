<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function app_config(): array
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }

    return $config;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    start_session();
    $_SESSION['_flash'][$type] = $message;
}

function get_flash(?string $type = null): mixed
{
    start_session();

    if ($type === null) {
        if (empty($_SESSION['_flash'])) {
            return null;
        }

        $messages = $_SESSION['_flash'];
        unset($_SESSION['_flash']);

        return $messages;
    }

    $message = $_SESSION['_flash'][$type] ?? null;
    if ($message !== null) {
        unset($_SESSION['_flash'][$type]);
    }

    if (isset($_SESSION['_flash']) && $_SESSION['_flash'] === []) {
        unset($_SESSION['_flash']);
    }

    return $message;
}

function render_flash_messages(): void
{
    start_session();

    if (empty($_SESSION['_flash'])) {
        return;
    }

    foreach ($_SESSION['_flash'] as $type => $message) {
        $class = $type === 'success' ? 'flash-success' : 'flash-error';
        echo '<div class="flash ' . $class . '">' . htmlspecialchars((string) $message) . '</div>';
    }

    unset($_SESSION['_flash']);
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_datetime(string $dateTime): string
{
    $timestamp = strtotime($dateTime);
    if ($timestamp === false) {
        return $dateTime;
    }

    return date('M j, Y g:i A', $timestamp);
}

function render_header(string $title, array $options = []): void
{
    $showNav = $options['show_nav'] ?? true;
    $user = $options['user'] ?? null;

    if ($user === null && $showNav) {
        $user = current_user();
    }

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="/styles.css">';
    echo '</head>';
    echo '<body>';

    echo '<header class="topbar">';
    echo '<div class="brand">';
    echo '<span class="brand__title">Biometric Attendance</span>';
    echo '</div>';

    if ($showNav && $user) {
        echo '<nav class="topbar__nav">';
        echo '<a href="/dashboard.php">Dashboard</a>';
        echo '<a href="/employees.php">Employees</a>';
        echo '<a href="/attendance_capture.php">Capture Attendance</a>';
        echo '<a href="/attendance_logs.php">Attendance Logs</a>';
        echo '<span class="nav-divider"></span>';
        echo '<span class="nav-user">' . h($user['name'] ?? $user['email']) . '</span>';
        echo '<a href="/logout.php" class="logout-link">Logout</a>';
        echo '</nav>';
    }

    echo '</header>';
    echo '<main class="container">';
}

function render_footer(): void
{
    echo '</main>';
    echo '<footer class="footer">&copy; ' . date('Y') . ' Biometric Attendance System</footer>';
    echo '</body>';
    echo '</html>';
}

function format_status_label(string $status): string
{
    return match ($status) {
        'check_in' => 'Check-In',
        'check_out' => 'Check-Out',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}
