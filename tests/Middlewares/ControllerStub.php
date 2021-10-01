<?php

namespace Sinbadxiii\PhalconAuth\Tests\Middlewares;

use Phalcon\Mvc\Controller;
use Sinbadxiii\PhalconAuth\Middlewares\Accessicate;

/**
 * Class ControllerMock
 * @package Sinbadxiii\PhalconAuth\Tests\Middlewares
 */
class ControllerStub extends Controller implements Accessicate
{
    public function authAccess(): bool
    {
        return true;
    }
}