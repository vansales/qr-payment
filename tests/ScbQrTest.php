<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/ScbQr.php';

/**
 * @property vansales\ScbQr ScbQr
 */
class ScbQrTest extends PHPUnit_Framework_TestCase {

  private $ScbQr;

  public function __construct() {
    $this->ScbQr = new vansales\ScbQr();
  }

}