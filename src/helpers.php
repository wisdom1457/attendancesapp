<?php

require_once __DIR__ . '/auth.php';

function render_header(string $title, ?array $user = null): void
{
    if (!$user) {
        $user = current_user();
    }

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<link rel="stylesheet" href="/styles.css">';
    echo '</head>';
    echo '<body>';
    echo '<header class="topbar">';
    echo '<h1>Hostel Management System</h1>';
    if ($user) {
        echo '<div class="user-info">Logged in as ' . htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['role']) . ')</div>';
        $links = ['<a href="/dashboard.php">Dashboard</a>'];
        if ($user['role'] === 'admin') {
            $links[] = '<a href="/admin_dashboard.php">Admin</a>';
        }
        $links[] = '<a href="/logout.php">Logout</a>';
        echo '<nav>' . implode(' | ', $links) . '</nav>';
    }
    echo '</header>';
    echo '<main class="container">';
}

function render_footer(): void
{
    echo '</main>';
    echo '<footer class="footer">&copy; ' . date('Y') . ' Hostel Management System</footer>';
    echo '</body></html>';
}

function render_flash_message(?string $message, string $type = 'error'): void
{
    if ($message) {
        $class = $type === 'success' ? 'flash-success' : 'flash-error';
        echo '<div class="flash ' . $class . '">' . htmlspecialchars($message) . '</div>';
    }
}
