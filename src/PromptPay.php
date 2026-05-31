<?php

namespace vansales;

require_once './vendor/autoload.php';

use Endroid\QrCode\QrCode;

/**
 * Original code logic from https://github.com/kittinan/php-promptpay-qr
 * More information https://www.vansalesapp.com
 */

class PromptPay
{

  const ID_PAYLOAD_FORMAT = '00';
  const ID_POI_METHOD = '01';
  const ID_MERCHANT_INFORMATION_BOT = '29';
  const ID_TRANSACTION_CURRENCY = '53';
  const ID_TRANSACTION_AMOUNT = '54';
  const ID_COUNTRY_CODE = '58';
  const ID_CRC = '63';

  const PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE = '01';
  const POI_METHOD_STATIC = '11';
  const POI_METHOD_DYNAMIC = '12';
  const MERCHANT_INFORMATION_TEMPLATE_ID_GUID = '00';
  const BOT_ID_MERCHANT_PHONE_NUMBER = '01';
  const BOT_ID_MERCHANT_TAX_ID = '02';
  const BOT_ID_MERCHANT_EWALLET_ID = '03';
  const GUID_PROMPTPAY = 'A000000677010111';
  const TRANSACTION_CURRENCY_THB = '764';
  const COUNTRY_CODE_TH = 'TH';

  public function generatePayload($target, $amount = null)
  {

    $target = $this->sanitizeTarget($target);

    $targetType = strlen($target) >= 15 ? self::BOT_ID_MERCHANT_EWALLET_ID : (strlen($target) >= 13 ? self::BOT_ID_MERCHANT_TAX_ID : self::BOT_ID_MERCHANT_PHONE_NUMBER);

    $data = [
      $this->f(self::ID_PAYLOAD_FORMAT, self::PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE),
      $this->f(self::ID_POI_METHOD, $amount ? self::POI_METHOD_DYNAMIC : self::POI_METHOD_STATIC),
      $this->f(self::ID_MERCHANT_INFORMATION_BOT, $this->serialize([
        $this->f(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, self::GUID_PROMPTPAY),
        $this->f($targetType, $this->formatTarget($target))
      ])),
      $this->f(self::ID_COUNTRY_CODE, self::COUNTRY_CODE_TH),
      $this->f(self::ID_TRANSACTION_CURRENCY, self::TRANSACTION_CURRENCY_THB),
    ];

    if ($amount !== null) {
      array_push($data, $this->f(self::ID_TRANSACTION_AMOUNT, $this->formatAmount($amount)));
    }

    $dataToCrc = $this->serialize($data) . self::ID_CRC . '04';
    array_push($data, $this->f(self::ID_CRC, $this->CRC16HexDigest($dataToCrc)));
    return $this->serialize($data);
  }

  public function f($id, $value)
  {
    return implode('', [$id, substr('00' . strlen($value), -2), $value]);
  }

  public function serialize($xs)
  {
    return implode('', $xs);
  }

  public function sanitizeTarget($str)
  {
    $str = preg_replace('/[^0-9]/', '', $str);
    return $str;
  }

  public function formatTarget($target)
  {

    $str = $this->sanitizeTarget($target);
    if (strlen($str) >= 13) {
      return $str;
    }

    $str = preg_replace('/^0/', '66', $str);
    $str = '0000000000000' . $str;

    return substr($str, -13);
  }

  public function formatAmount($amount)
  {
    return number_format($amount, 2, '.', '');
  }

  public function crc16($data)
  {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
      $x = (($crc >> 8) ^ ord($data[(string)$i])) & 0xFF;
      $x ^= $x >> 4;
      $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
    }
    return $crc;
  }

  /*
  * Returns CRC16 of a string as hexadecimal string 
  */
  public function CRC16HexDigest($str)
  {
    return sprintf('%04X', $this->crc16($str));
  }


  public function generateQrCode($target, $amount = 0)
  {

    $payload = $this->generatePayload($target, $amount);

    $qrCode = new QrCode($payload);
    header('Content-Type: ' . $qrCode->getContentType());
    echo $qrCode->writeString();
  }

  /**
   * True when $target is a full EMV QR payload (a merchant Bill Payment QR
   * that the company stored verbatim), not a proxy number (phone / ID card).
   * Proxy targets are short and digits-only, so they never start with "000201".
   */
  public function isMerchantPayload($target)
  {
    $target = (string) $target;
    return strlen($target) >= 30 && strpos($target, '000201') === 0;
  }

  /**
   * Parse an EMV QR string into a flat list of [id, value] top-level tags.
   */
  public function parseTlv($payload)
  {
    $out = [];
    $i = 0;
    $n = strlen($payload);
    while ($i + 4 <= $n) {
      $id  = substr($payload, $i, 2);
      $len = (int) substr($payload, $i + 2, 2);
      $val = substr($payload, $i + 4, $len);
      if (strlen($val) < $len) {
        break; // malformed / truncated — stop
      }
      $out[] = [$id, $val];
      $i += 4 + $len;
    }
    return $out;
  }

  /**
   * Re-inject an amount into a merchant Bill Payment payload WITHOUT touching
   * the merchant account block (Tag 30 = biller id + ref1 + ref2). We only:
   *   - flip POI (Tag 01) 11 (static) -> 12 (dynamic) when an amount is given
   *   - insert/replace Tag 54 (amount) right after Tag 53 (currency)
   *   - recompute the CRC (Tag 63)
   * Every other tag is preserved byte-for-byte so the bank routing is identical.
   */
  public function generateMerchantPayload($payload, $amount = null)
  {
    $data = [];
    foreach ($this->parseTlv($payload) as $field) {
      list($id, $val) = $field;

      if ($id === self::ID_CRC) {
        continue; // drop old CRC, recomputed below
      }
      if ($id === self::ID_TRANSACTION_AMOUNT) {
        continue; // drop any existing amount, re-injected after currency
      }
      if ($id === self::ID_POI_METHOD) {
        $val = $amount ? self::POI_METHOD_DYNAMIC : self::POI_METHOD_STATIC;
      }

      $data[] = $this->f($id, $val);

      if ($id === self::ID_TRANSACTION_CURRENCY && $amount !== null) {
        $data[] = $this->f(self::ID_TRANSACTION_AMOUNT, $this->formatAmount($amount));
      }
    }

    $dataToCrc = $this->serialize($data) . self::ID_CRC . '04';
    $data[]    = $this->f(self::ID_CRC, $this->CRC16HexDigest($dataToCrc));
    return $this->serialize($data);
  }

  public function generateMerchantQrCode($payload, $amount = 0)
  {
    $newPayload = $this->generateMerchantPayload($payload, $amount);

    $qrCode = new QrCode($newPayload);
    header('Content-Type: ' . $qrCode->getContentType());
    echo $qrCode->writeString();
  }
}
