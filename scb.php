<?php 
require_once './src/ScbQr.php';
$scb = new vansales\ScbQr();

// Grab parameter from URI
// ?amount=99.25&ref_1=CUST1100&ref_2=INV1001&billerId=0115311040039475101
$amount = $_GET['amount'] ?? 0;
$ref_1 = $_GET['ref_1'] ?? 'none';
$ref_2 = $_GET['ref_2'] ?? 'none';

# '0115311040039475101'; // Biller ID TEST1
$billerId = $_GET['billerId'] ?? '0115311040039475101'; 

// Display qrcode as PNG image
$scb->getqrcode($amount, $ref_1, $ref_2, $billerId);




