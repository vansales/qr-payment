# vansales/qr-payment
[![Build Status](https://travis-ci.org/vansales/qr-payment.svg?branch=master)](https://travis-ci.org/vansales/qr-payment)
[![Build Status](https://scrutinizer-ci.com/g/vansales/qr-payment/badges/build.png?b=master)](https://scrutinizer-ci.com/g/vansales/qr-payment/build-status/master)
[![Build Status](https://scrutinizer-ci.com/g/vansales/qr-payment/badges/build.png?b=master)](https://scrutinizer-ci.com/g/vansales/qr-payment/build-status/master)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

PHP Library to generate QR Code for PromptPay and SCB Payment

## Requirement
- PHP 7.2+
- [GD Extension](http://php.net/manual/en/book.image.php) (For Generate QR Code)

## Composer
This package available on [Packagist](https://packagist.org/packages/vansales/qr-payment), Install the latest version with composer 

```
composer require vansales/qr-payment
```

## Usage

```php
# To generate PromptPay QR Code
$promptpay = new vansales\PromptPay();

// Grab parameter from URI
// ?amount=99.25&targer=0823456789
$amount = $_GET['amount'] ?? 120.05;
$target = $_GET['target'] ?? '0823456789';

// Display qrcode as PNG image
$promptpay->generateQrCode($target, $amount);


# To generate SCB Payment QR Code
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
```

## Sample Generated PromptPay QR Code
<p align="center">
  <img src="images/promptpay.png" width="250" />
</p>

## Contributing
Feel free to contribute on this project, We'll be happy to work with you.

## License
The MIT License (MIT)
