<?php

require_once __DIR__ . '/../src/helpers.php';

$user = require_login();

$config = require __DIR__ . '/../src/config.php';
$sslConfig = $config['sslcommerz'];

$error = null;
$formData = [
    'student_name' => '',
    'student_id' => '',
    'payment_type' => '',
    'amount' => '',
    'phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['student_name'] = trim($_POST['student_name'] ?? '');
    $formData['student_id'] = trim($_POST['student_id'] ?? '');
    $formData['payment_type'] = $_POST['payment_type'] ?? '';
    $formData['amount'] = trim($_POST['amount'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');

    $amountValue = is_numeric($formData['amount']) ? (float) $formData['amount'] : 0.0;

    if ($formData['student_name'] === '' || $formData['student_id'] === '') {
        $error = 'Student name and ID are required.';
    } elseif (!in_array($formData['payment_type'], ['tuition', 'exam', 'activities'], true)) {
        $error = 'Please choose a valid payment type.';
    } elseif ($amountValue <= 0) {
        $error = 'Enter a valid payment amount.';
    } elseif (!$sslConfig['store_id'] || !$sslConfig['store_password']) {
        $error = 'Payment gateway credentials are not configured.';
    } else {
        $transactionId = 'TXN-' . date('YmdHis') . '-' . random_int(1000, 9999);

        $paymentLabel = [
            'tuition' => 'Tuition Fee',
            'exam' => 'Exam Fee',
            'activities' => 'Activity Fee',
        ][$formData['payment_type']];

        $payload = [
            'store_id' => $sslConfig['store_id'],
            'store_passwd' => $sslConfig['store_password'],
            'total_amount' => number_format($amountValue, 2, '.', ''),
            'currency' => $sslConfig['currency'],
            'tran_id' => $transactionId,
            'success_url' => $sslConfig['base_url'] . '/payment_status.php?result=success',
            'fail_url' => $sslConfig['base_url'] . '/payment_status.php?result=failed',
            'cancel_url' => $sslConfig['base_url'] . '/payment_status.php?result=cancelled',
            'cus_name' => $user['name'],
            'cus_email' => $user['email'],
            'cus_add1' => 'N/A',
            'cus_city' => 'Dhaka',
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $formData['phone'] ?: '00000000000',
            'shipping_method' => 'NO',
            'product_name' => $paymentLabel,
            'product_category' => 'Education',
            'product_profile' => 'non-physical-goods',
            'value_a' => $formData['student_name'],
            'value_b' => $formData['student_id'],
            'value_c' => $formData['payment_type'],
        ];

        $apiUrl = $sslConfig['sandbox']
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $error = 'Unable to initiate the payment. ' . $curlError;
        } else {
            $decoded = json_decode($response, true);

            if (isset($decoded['status']) && $decoded['status'] === 'SUCCESS' && !empty($decoded['GatewayPageURL'])) {
                header('Location: ' . $decoded['GatewayPageURL']);
                exit;
            }

            if (isset($decoded['failedreason'])) {
                $error = 'Payment initialization failed: ' . $decoded['failedreason'];
            } else {
                $error = 'Unexpected response from SSLCommerz gateway.';
            }
        }
    }
}

render_header('Make a Payment', $user);
?>

<section class="card">
    <h2>Pay Student Fees</h2>
    <p>Use the form below to pay tuition, exam, or extracurricular activity fees securely through SSLCommerz.</p>
    <?php render_flash_message($error); ?>
    <form method="post" class="payment-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="student_name">Student name</label>
                <input type="text" id="student_name" name="student_name" required value="<?php echo htmlspecialchars($formData['student_name']); ?>">
            </div>
            <div class="form-group">
                <label for="student_id">Student ID</label>
                <input type="text" id="student_id" name="student_id" required value="<?php echo htmlspecialchars($formData['student_id']); ?>">
            </div>
            <div class="form-group">
                <label for="payment_type">Payment type</label>
                <select id="payment_type" name="payment_type" required>
                    <option value="" disabled <?php echo $formData['payment_type'] === '' ? 'selected' : ''; ?>>Select payment</option>
                    <option value="tuition" <?php echo $formData['payment_type'] === 'tuition' ? 'selected' : ''; ?>>Tuition fee</option>
                    <option value="exam" <?php echo $formData['payment_type'] === 'exam' ? 'selected' : ''; ?>>Exam fee</option>
                    <option value="activities" <?php echo $formData['payment_type'] === 'activities' ? 'selected' : ''; ?>>Extracurricular activities</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Amount (<?php echo htmlspecialchars($sslConfig['currency']); ?>)</label>
                <input type="number" min="0" step="0.01" id="amount" name="amount" required value="<?php echo htmlspecialchars($formData['amount']); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Contact phone (optional)</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>">
            </div>
        </div>
        <button type="submit">Proceed to payment</button>
    </form>
    <p class="hint">You will be redirected to the SSLCommerz gateway to complete the transaction.</p>
</section>

<?php
render_footer();
