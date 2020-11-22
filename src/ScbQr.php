<?php

namespace vansales;

require_once './vendor/autoload.php';

use Endroid\QrCode\QrCode;


class ScbQr
{

    const RELEASE_VERSION_OF_QR = '000201';
    const INITIAL_METHOD_OF_QR = '010212';
    const SCB_PAYMENT_CODE = '0016A000000677010112';
    const SCB_REFERENCE_CODE = '0706SCB001';
    const TRANSACTION_CURRENCY_THB = '5303764';
    const COUNTRY_CODE_TH = '5802TH';
    const CHECKSUM_PREFIX = '6304';

    public function getqrcode($amount = 0, $ref_1 = 'none', $ref_2 = 'none', $billerId = '')
    {

        $command = '';

        # Release version of QR
        $command .= self::RELEASE_VERSION_OF_QR;

        # Initial Method of QR
        $command .= self::INITIAL_METHOD_OF_QR;

        # Reference Code
        $ref_01 = $this->f('02',  $ref_1);
        $ref_02 = $this->f('03',  $ref_2);
        $ref_command = self::SCB_PAYMENT_CODE . $billerId . $ref_01 . $ref_02;

        $command .=  $this->f('30', $ref_command);

        # สกุลเงินบาท
        $command .= self::TRANSACTION_CURRENCY_THB;

        # จำนวนเงิน
        $command .= $this->f('54', $this->formatAmount($amount));

        # CountryCode “TH”
        $command .= self::COUNTRY_CODE_TH;

        # SCB reference ไว้ใช้อ้างอิงระหว่าง ร้านค้ากับธนาคาร
        $command .= $this->f('62', self::SCB_REFERENCE_CODE);

        # CRC16 checksum
        $command .= self::CHECKSUM_PREFIX;
        $command .= $this->CRC16HexDigest($command);

        $qrCode = new QrCode($command);

        header('Content-Type: ' . $qrCode->getContentType());
        echo $qrCode->writeString();
    }

    private function f($prefix, $data)
    {
        return $prefix . sprintf("%02d", strlen($data)) . $data;
    }

    public function formatAmount($amount)
    {
        return number_format($amount, 2, '.', '');
    }

    public function crc16($data)
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return $crc;
    }

    /*
    * Returns CRC16 of a string as hexadecimal string 
    */
    private function CRC16HexDigest($str)
    {
        return sprintf('%04X', $this->crc16($str));
    }
}
