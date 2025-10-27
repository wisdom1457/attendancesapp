<?php

require_once __DIR__ . '/../src/helpers.php';

start_session();
$user = current_user();

$result = $_GET['result'] ?? 'unknown';
$message = 'We were unable to determine the payment status.';
$type = 'error';

if ($result === 'success') {
    $message = 'Thank you! Your payment was completed successfully. Please allow a few moments for it to appear in our records.';
    $type = 'success';
} elseif ($result === 'failed') {
    $message = 'The payment attempt failed. You can return to the payment page to try again.';
} elseif ($result === 'cancelled') {
    $message = 'The payment was cancelled before completion. No charges were made.';
}

render_header('Payment Status', $user);
?>

<section class="card">
    <h2>Payment status</h2>
    <?php render_flash_message($message, $type); ?>
    <p>If you have any questions, please contact the administration office with your transaction details.</p>
    <p><a class="button" href="/payment.php">Return to payment page</a></p>
</section>

<?php
render_footer();
