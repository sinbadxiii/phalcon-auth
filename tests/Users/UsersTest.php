<?php

namespace Sinbadxiii\PhalconAuth\Tests\Users;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\Contracts\RememberingInterface;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class UsersTest
 * @package Sinbadxiii\PhalconAuth\Tests\Users
 */
class UsersTest extends AbstractTestCase
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