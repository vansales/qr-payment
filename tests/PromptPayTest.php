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

}