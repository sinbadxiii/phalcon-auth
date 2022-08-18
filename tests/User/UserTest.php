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
        $userStub = new UserModelStub();

        $this->assertInstanceOf(AuthenticatableInterface::class, $userStub);
        $this->assertInstanceOf(RememberingInterface::class, $userStub);
    }

    /**
     * @test
     */
    public function itReturnsSameRememberToken(): void
    {
        $userStub = new UserModelStub();

        $rememberToken = new RememberTokenModelStub();
        $rememberToken->token = "Token";

        $userStub->setRememberToken($rememberToken);
        $this->assertSame($rememberToken, $userStub->getRememberToken());
    }

    /**
     * @test
     */
    public function itReturnsSameRememberTokenString(): void
    {
        $userStub = new UserModelStub();

        $rememberToken = new RememberTokenModelStub();
        $rememberToken->token = "Token";

        $userStub->setRememberToken($rememberToken);
        $this->assertEquals("Token", $userStub->getRememberToken()->getToken());
    }
}