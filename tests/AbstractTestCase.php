<?php

namespace Sinbadxiii\PhalconAuth\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTestCase
 * @package Sinbadxiii\PhalconAuth\Tests
 */
abstract class AbstractTestCase extends TestCase
{
    public function flushAll()
    {
        $_SERVER  = [];
        $_REQUEST = [];
        $_POST    = [];
        $_GET     = [];
    }
}