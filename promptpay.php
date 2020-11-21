<?php 
require_once './src/PromptPay.php';
$promptpay = new vansales\PromptPay();

// Grab parameter from URI
// ?amount=99.25&targer=0826269966
$amount = $_GET['amount'] ?? 120.05;
$target = $_GET['target'] ?? '0826269966';

// Display qrcode as PNG image
$promptpay->generateQrCode($target, $amount);