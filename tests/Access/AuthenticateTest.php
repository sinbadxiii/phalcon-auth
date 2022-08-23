<?php

namespace Sinbadxiii\PhalconAuth\Tests\Access;

use Phalcon\Di\Injectable;
use Sinbadxiii\PhalconAuth\Access\Authenticate;
use Sinbadxiii\PhalconAuth\Access\AuthenticatesRequest;
use Sinbadxiii\PhalconAuth\Access\AuthenticatesRequestInterface;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AuthenticateTest
 * @package Sinbadxiii\PhalconAuth\Tests\Access
 */
class AuthenticateTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementFromAuthenticatesRequest(): void
    {
        $this->assertInstanceOf(AuthenticatesRequestInterface::class, new Authenticate());
        $this->assertInstanceOf(Injectable::class, new Authenticate());
    }
}