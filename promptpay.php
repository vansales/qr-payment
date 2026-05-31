<?php 
require_once './src/PromptPay.php';
$promptpay = new vansales\PromptPay();

// Grab parameter from URI
// Sample url parameter
// ?amount=99.25&targer=0826269966
$amount = $_GET['amount'] ?? 120.05;
$target = $_GET['target'] ?? '0826269966';

// Display qrcode as PNG image.
// A merchant Bill Payment QR (e.g. GSB) is stored verbatim as the target —
// it is a full EMV payload, so re-inject the amount instead of treating it as
// a proxy number. Proxy targets (phone / ID card) are unchanged.
if ($promptpay->isMerchantPayload($target)) {
    $promptpay->generateMerchantQrCode($target, $amount);
} else {
    $promptpay->generateQrCode($target, $amount);
}

