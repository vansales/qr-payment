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

  public function testformatAmount() {

    $amount = 90.2511;
    $result = $this->PromptPay->formatAmount($amount);
    $expected = '90.25';
    $this->assertEquals($expected, $result);

    $amount = 90.2561;
    $result = $this->PromptPay->formatAmount($amount);
    $expected = '90.26';
    $this->assertEquals($expected, $result);
  }

}