<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/PromptPay.php';

/**
 * @property vansales\PromptPay PromptPay
 */
class PromptPayTest extends PHPUnit_Framework_TestCase {

  private $PromptPay;

  public function __construct() {
    $this->PromptPay = new vansales\PromptPay();
  }

  public function testFormatTarget() {

    //Target phone number
    $target = '0899999999';
    $result = $this->PromptPay->formatTarget($target);
    $expected = '0066899999999';
    $this->assertEquals($expected, $result);

    $target = '089-999-9999';
    $result = $this->PromptPay->formatTarget($target);
    $expected = '0066899999999';
    $this->assertEquals($expected, $result);

    //Target ID
    $target = '1234567890123';
    $result = $this->PromptPay->formatTarget($target);
    $expected = '1234567890123';
    $this->assertEquals($expected, $result);
    
    //Target Tax ID start with 0 (Coporate Tax ID)
    $target = '0123456789012';
    $result = $this->PromptPay->formatTarget($target);
    $expected = '0123456789012';
    $this->assertEquals($expected, $result);
  }

  public function testformatAmount() {

    $amount = 90.2511;
    $result = $this->PromptPay->formatAmount($amount);
    $expected = '90.25';
    $this->assertEquals($expected, $result);

    $amount = 555.555;
    $result = $this->ScbQr->formatAmount($amount);
    $expected = '555.56';
    $this->assertEquals($expected, $result);
  }

  public function testCrc16() {

    // https://www.lammertbies.nl/comm/info/crc-calculation
    $data = '00020101021230570016A00000067701011201153110400394751010206REF0010304REF253037645406555.555802TH62100706SCB0016304';
    $crc16 = $this->PromptPay->crc16($data);
    $result = $this->PromptPay->CRC16HexDigest($crc16);
    $expected = '37C6';
    $this->assertEquals($expected, $result);
  }

}

