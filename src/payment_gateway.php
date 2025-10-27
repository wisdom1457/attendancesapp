<?php

require_once __DIR__ . '/helpers.php';

class PaymentGatewayException extends RuntimeException
{
}

function initiate_sslcommerz_payment(array $paymentRequest): array
{
    $config = sslcommerz_config();
    $storeId = $config['store_id'] ?? '';
    $storePassword = $config['store_password'] ?? '';

    if ($storeId === '' || $storePassword === '') {
        throw new PaymentGatewayException('SSLCommerz credentials are not configured. Set SSLCOMMERZ_STORE_ID and SSLCOMMERZ_STORE_PASSWORD.');
    }

    $apiBase = !empty($config['sandbox_mode']) ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';
    $gatewayUrl = $apiBase . '/gwprocess/v4/api.php';

    $amount = number_format((float) ($paymentRequest['amount'] ?? 0), 2, '.', '');
    if ($amount <= 0) {
        throw new PaymentGatewayException('Invalid payment amount.');
    }

    $transactionId = $paymentRequest['transaction_id'] ?? uniqid('txn_', true);
    $currency = $config['default_currency'] ?? 'BDT';

    $payload = [
        'store_id' => $storeId,
        'store_passwd' => $storePassword,
        'total_amount' => $amount,
        'currency' => $currency,
        'tran_id' => $transactionId,
        'success_url' => $paymentRequest['success_url'] ?? base_url('payment_status.php?status=success'),
        'fail_url' => $paymentRequest['fail_url'] ?? base_url('payment_status.php?status=failed'),
        'cancel_url' => $paymentRequest['cancel_url'] ?? base_url('payment_status.php?status=cancelled'),
        'cus_name' => $paymentRequest['customer_name'] ?? 'Parent/Guardian',
        'cus_email' => $paymentRequest['customer_email'] ?? 'parent@example.com',
        'cus_phone' => $paymentRequest['customer_phone'] ?? '00000000000',
        'cus_add1' => $paymentRequest['customer_address'] ?? 'N/A',
        'product_category' => 'education',
        'product_name' => $paymentRequest['product_name'] ?? 'Student fee payment',
        'shipping_method' => 'NO',
        'num_of_item' => 1,
        'product_profile' => 'non-physical-goods',
        'value_a' => $paymentRequest['child_name'] ?? '',
        'value_b' => $paymentRequest['payment_type'] ?? '',
        'value_c' => $paymentRequest['note'] ?? '',
        'value_d' => $paymentRequest['student_id'] ?? '',
    ];

    if (!empty($paymentRequest['customer_city'])) {
        $payload['cus_city'] = $paymentRequest['customer_city'];
    }
    if (!empty($paymentRequest['customer_postcode'])) {
        $payload['cus_postcode'] = $paymentRequest['customer_postcode'];
    }

    $ch = curl_init($gatewayUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

    $response = curl_exec($ch);
    if ($response === false) {
        $errorMessage = curl_error($ch) ?: 'Unknown cURL error';
        curl_close($ch);
        throw new PaymentGatewayException('Failed to contact SSLCommerz: ' . $errorMessage);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new PaymentGatewayException('SSLCommerz returned HTTP status ' . $httpCode . '.');
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new PaymentGatewayException('Invalid response from SSLCommerz: ' . $response);
    }

    if (($decoded['status'] ?? '') !== 'SUCCESS' || empty($decoded['GatewayPageURL'])) {
        $failedReason = $decoded['failedreason'] ?? 'Unknown error';
        throw new PaymentGatewayException('SSLCommerz could not initiate the payment: ' . $failedReason);
    }

    return $decoded;
}
