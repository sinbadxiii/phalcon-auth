<?php

namespace Sinbadxiii\PhalconAuth\Tests\Middleware;

use Phalcon\Di\Injectable;
use Sinbadxiii\PhalconAuth\Access\Authenticate;
use Sinbadxiii\PhalconAuth\Access\AuthenticatesRequest;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AuthenticateTest
 * @package Sinbadxiii\PhalconAuth\Tests\Middleware
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