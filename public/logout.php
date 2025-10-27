<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

logout();
set_flash('success', 'You have been signed out.');
redirect('/index.php');
