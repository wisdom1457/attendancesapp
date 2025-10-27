<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/payment_gateway.php';

$user = require_login();

$feeOptions = [
    'tuition_fee' => [
        'label' => 'Tuition fee',
        'amount' => 5000,
        'description' => 'Covers the monthly tuition for your child.',
    ],
    'exam_fee' => [
        'label' => 'Exam fee',
        'amount' => 1500,
        'description' => 'Exam registration and administration charges.',
    ],
    'activities' => [
        'label' => 'Co-curricular activities',
        'amount' => 1000,
        'description' => 'Covers club memberships, sports, and cultural events.',
    ],
];

$error = null;
$validationErrors = [];
$selectedPaymentType = $_POST['payment_type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $childName = trim($_POST['child_name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $paymentType = $_POST['payment_type'] ?? '';
    $amountInput = trim($_POST['amount'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($childName === '') {
        $validationErrors['child_name'] = 'Please provide your child\'s full name.';
    }

    if ($paymentType === '' || !array_key_exists($paymentType, $feeOptions)) {
        $validationErrors['payment_type'] = 'Choose a valid payment category.';
    }

    $amount = $amountInput !== '' ? (float) $amountInput : ($feeOptions[$paymentType]['amount'] ?? 0);
    if ($amount <= 0) {
        $validationErrors['amount'] = 'Enter a valid amount for this payment.';
    }

    if ($phone === '') {
        $validationErrors['phone'] = 'A contact phone number is required for SSLCommerz.';
    }

    if (!$validationErrors) {
        try {
            $response = initiate_sslcommerz_payment([
                'amount' => $amount,
                'transaction_id' => uniqid('tuition_', true),
                'child_name' => $childName,
                'payment_type' => $paymentType,
                'student_id' => $studentId,
                'product_name' => $feeOptions[$paymentType]['label'] ?? 'Student fee payment',
                'note' => $note,
                'customer_name' => $user['name'],
                'customer_email' => $user['email'],
                'customer_phone' => $phone,
                'customer_address' => $address ?: 'Parent address not provided',
                'customer_city' => $_POST['city'] ?? '',
                'customer_postcode' => $_POST['postcode'] ?? '',
                'success_url' => base_url('payment_status.php?status=success'),
                'fail_url' => base_url('payment_status.php?status=failed'),
                'cancel_url' => base_url('payment_status.php?status=cancelled'),
            ]);

            header('Location: ' . $response['GatewayPageURL']);
            exit;
        } catch (PaymentGatewayException $exception) {
            $error = $exception->getMessage();
        }
    }
}

render_header('Make a payment', $user);
?>
<div class="card">
    <h2>Pay school fees with SSLCommerz</h2>
    <p class="hint">Select the payment category below. You will be redirected to SSLCommerz to complete the payment securely.</p>
    <?php
    if ($error) {
        render_flash_message($error, 'error');
    }
    ?>
    <form method="post" class="payment-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="child_name">Child's name</label>
                <input type="text" id="child_name" name="child_name" value="<?php echo htmlspecialchars($_POST['child_name'] ?? ''); ?>" required>
                <?php if (isset($validationErrors['child_name'])): ?>
                    <p class="field-error"><?php echo htmlspecialchars($validationErrors['child_name']); ?></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="student_id">Student ID (optional)</label>
                <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
            </div>
        </div>

        <fieldset class="payment-options">
            <legend>Select a fee type</legend>
            <?php foreach ($feeOptions as $key => $option): ?>
                <label class="payment-option">
                    <input type="radio" name="payment_type" value="<?php echo htmlspecialchars($key); ?>" <?php echo ($selectedPaymentType === $key) ? 'checked' : ''; ?> required>
                    <span class="payment-option__title"><?php echo htmlspecialchars($option['label']); ?></span>
                    <span class="payment-option__amount">Default: <?php echo number_format($option['amount'], 2); ?> BDT</span>
                    <span class="payment-option__description"><?php echo htmlspecialchars($option['description']); ?></span>
                </label>
            <?php endforeach; ?>
            <?php if (isset($validationErrors['payment_type'])): ?>
                <p class="field-error"><?php echo htmlspecialchars($validationErrors['payment_type']); ?></p>
            <?php endif; ?>
        </fieldset>

        <div class="form-grid">
            <div class="form-group">
                <label for="amount">Amount (BDT)</label>
                <input type="number" step="0.01" min="0" id="amount" name="amount" value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" placeholder="Will default to the selected fee amount">
                <?php if (isset($validationErrors['amount'])): ?>
                    <p class="field-error"><?php echo htmlspecialchars($validationErrors['amount']); ?></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="phone">Guardian phone number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                <?php if (isset($validationErrors['phone'])): ?>
                    <p class="field-error"><?php echo htmlspecialchars($validationErrors['phone']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="address">Billing address</label>
                <textarea id="address" name="address" rows="3" placeholder="Street, area, district"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($_POST['postcode'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="note">Additional note (optional)</label>
            <textarea id="note" name="note" rows="3" placeholder="Any instruction for the accounts department"><?php echo htmlspecialchars($_POST['note'] ?? ''); ?></textarea>
        </div>

        <button type="submit">Proceed to SSLCommerz</button>
    </form>
</div>

<section class="card">
    <h3>How online payments work</h3>
    <ol class="payment-steps">
        <li>Fill in the fee details above and choose the category you want to pay for.</li>
        <li>Review the amount and click <strong>Proceed to SSLCommerz</strong>.</li>
        <li>You will be taken to the secure SSLCommerz checkout to finish the payment.</li>
        <li>After payment, you will return to this portal with a confirmation message.</li>
    </ol>
</section>

<script>
    const feeOptions = <?php echo json_encode(array_map(static function ($option) {
        return $option['amount'];
    }, $feeOptions), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const paymentTypeInputs = document.querySelectorAll('input[name="payment_type"]');
    const amountInput = document.getElementById('amount');

    if (amountInput) {
        amountInput.dataset.autoFilled = amountInput.dataset.autoFilled || 'false';
    }

    const checkedOption = document.querySelector('input[name="payment_type"]:checked');
    if (checkedOption && amountInput && amountInput.value === '') {
        const defaultAmount = feeOptions[checkedOption.value];
        if (defaultAmount) {
            amountInput.value = Number(defaultAmount).toFixed(2);
            amountInput.dataset.autoFilled = 'true';
        }
    }

    paymentTypeInputs.forEach((input) => {
        input.addEventListener('change', () => {
            const defaultAmount = feeOptions[input.value];
            if (defaultAmount && (amountInput.value === '' || amountInput.dataset.autoFilled === 'true')) {
                amountInput.value = defaultAmount.toFixed(2);
                amountInput.dataset.autoFilled = 'true';
            }
        });
    });

    amountInput.addEventListener('input', () => {
        amountInput.dataset.autoFilled = 'false';
    });
</script>
<?php
render_footer();
