<?php
require_once __DIR__ . '/../src/helpers.php';

$status = strtolower($_GET['status'] ?? $_POST['status'] ?? '');
$transactionId = $_POST['tran_id'] ?? $_GET['tran_id'] ?? null;
$amount = $_POST['amount'] ?? $_GET['amount'] ?? null;
$currency = $_POST['currency'] ?? $_GET['currency'] ?? null;
$cardType = $_POST['card_type'] ?? null;

$user = current_user();

$message = '';
$messageType = 'error';
$detailsNote = '';

switch ($status) {
    case 'success':
        $message = 'Thank you! SSLCommerz has reported a successful payment.';
        $messageType = 'success';
        $detailsNote = 'Our accounts team will reconcile the transaction and update your child\'s ledger shortly.';
        break;
    case 'failed':
        $message = 'Unfortunately the payment attempt was not completed.';
        $detailsNote = 'Please try again or contact your bank if the issue persists.';
        break;
    case 'cancelled':
        $message = 'The payment process was cancelled before completion.';
        $detailsNote = 'You can restart the payment any time from the payments page.';
        break;
    default:
        $message = 'We could not determine the payment status. If you completed a transaction, please check your email for a confirmation from SSLCommerz.';
        $detailsNote = 'Contact the school accounts office with the transaction ID if you have concerns about duplicate charges.';
        break;
}

render_header('Payment status', $user);
?>
<div class="card">
    <h2>Payment update</h2>
    <?php render_flash_message($message, $messageType); ?>
    <p><?php echo htmlspecialchars($detailsNote); ?></p>

    <dl class="payment-summary">
        <?php if ($transactionId): ?>
            <div class="payment-summary__row">
                <dt>Transaction ID</dt>
                <dd><?php echo htmlspecialchars($transactionId); ?></dd>
            </div>
        <?php endif; ?>
        <?php if ($amount): ?>
            <div class="payment-summary__row">
                <dt>Amount</dt>
                <dd><?php echo htmlspecialchars(number_format((float) $amount, 2) . ($currency ? ' ' . $currency : '')); ?></dd>
            </div>
        <?php endif; ?>
        <?php if ($cardType): ?>
            <div class="payment-summary__row">
                <dt>Card type</dt>
                <dd><?php echo htmlspecialchars($cardType); ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <p><a class="button-link" href="/payment.php">Return to payments</a></p>
</div>

<section class="card">
    <h3>Next steps</h3>
    <ul>
        <li>Successful transactions will also appear in your email as a receipt from SSLCommerz.</li>
        <li>If you believe you were charged but see a failure message, contact the school accounts office with the transaction ID.</li>
        <li>Remember that the payment is not final until it is validated from SSLCommerz&#8217;s IPN or validation API.</li>
    </ul>
</section>
<?php
render_footer();
