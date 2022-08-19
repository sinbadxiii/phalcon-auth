<?php

namespace Sinbadxiii\PhalconAuth\Tests\User;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class UsersTest
 * @package Sinbadxiii\PhalconAuth\Tests\User
 */
class UserTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function itImplementModel(): void
    {
        $userFake = new UserModelFake();

        $this->assertInstanceOf(AuthenticatableInterface::class, $userFake);
        $this->assertInstanceOf(RememberingInterface::class, $userFake);
    }

    /**
     * @test
     */
    public function itReturnsSameRememberToken(): void
    {
        $userFake = new UserModelFake();

        $rememberToken = new RememberTokenModelFake();
        $rememberToken->token = "Token";

        $userFake->setRememberToken($rememberToken);
        $this->assertSame($rememberToken, $userFake->getRememberToken());
    }

    /**
     * @test
     */
    public function itReturnsSameRememberTokenString(): void
    {
        $userFake = new UserModelFake();

        $rememberToken = new RememberTokenModelFake();
        $rememberToken->token = "Token";

        $userFake->setRememberToken($rememberToken);
        $this->assertEquals("Token", $userFake->getRememberToken()->getToken());
    }
}