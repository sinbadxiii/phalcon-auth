<?php

namespace Sinbadxiii\PhalconAuth\Tests\Middlewares;

use Phalcon\Di\Injectable;
use Sinbadxiii\PhalconAuth\Middlewares\Authenticate;
use Sinbadxiii\PhalconAuth\Middlewares\AuthenticatesRequest;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AuthenticateTest
 * @package Sinbadxiii\PhalconAuth\Tests\Middlewares
 */
class AuthenticateTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementFromAuthenticatesRequest(): void
    {
        $this->assertInstanceOf(AuthenticatesRequest::class, new Authenticate());
        $this->assertInstanceOf(Injectable::class, new Authenticate());
    }
}