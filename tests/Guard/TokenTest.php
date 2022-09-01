<?php

namespace Sinbadxiii\PhalconAuth\Tests\Guard;

use Phalcon\Encryption\Security;
use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Guard\BasicAuthInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardStatefulInterface;
use Sinbadxiii\PhalconAuth\Guard\Token;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;


/**
 * Class TokenTest
 * @package Sinbadxiii\PhalconAuth\Tests\Adapter
 */
class TokenTest extends AbstractTestCase
{
    protected $tokenGuard;
    protected $adapter;

    protected function setUp(): void
    {
        $security = new Security();

        $сonfigAdapter = [
                'data' => [
                    ['email' => 'user@user.ru', "password" => "12345", "id" => 0, "auth_token" => "token"],
                    ['email' => 'user1@user1.ru', "password" => "1234", "id" => 1 , "auth_token" => "token1"],
                ],
                'model' => UserModelFake::class
            ];

        $adapter  = new Memory($security, $сonfigAdapter);

        $token = new Token(
            $adapter,
            [
                'inputKey'  => 'auth_token',
                'storageKey' => 'auth_token',
            ],
            new Request(),
        );

        $this->tokenGuard   = $token;
        $this->adapter      = $adapter;
    }

    /** @test */
    public function constructor(): void
    {
        $this->assertEquals("Sinbadxiii\PhalconAuth\Guard\Token", $this->tokenGuard::class);
    }

    /** @test */
    public function implementFromGuardInterface(): void
    {
        $this->assertInstanceOf(GuardInterface::class, $this->tokenGuard);
    }

    /** @test */
    public function notImplementFromGuardStatefulInterface(): void
    {
        $this->assertNotInstanceOf(GuardStatefulInterface::class, $this->tokenGuard);
    }

    /** @test */
    public function implementFromBasicAuthInterface(): void
    {
        $this->assertNotInstanceOf(BasicAuthInterface::class, $this->tokenGuard);
    }

    /** @test */
    public function gettingAdapterMemory(): void
    {
        $this->assertEquals($this->adapter, $this->tokenGuard->getAdapter());
    }

    /** @test */
    public function settingAdapterMemory(): void
    {
        $this->tokenGuard->setAdapter($this->adapter);
        $this->assertEquals($this->adapter, $this->tokenGuard->getAdapter());
    }

    /** @test */
    public function nullUserWithoutAuthToken(): void
    {
        $user = $this->tokenGuard->user();
        $this->assertNull($user);
    }

    /** @test */
    public function userWithAuthTokenInRequest(): void
    {
        $_REQUEST["auth_token"] = "token";

        $this->assertEquals(new UserModelFake(
            ['email' => 'user@user.ru', "password" => "12345", "id" => 0, "auth_token" => "token"]
        ), $this->tokenGuard->user());
    }

    /** @test */
    public function userWithAuthTokenInRequestPost(): void
    {
        $_POST["auth_token"] = "token";

        $this->assertEquals(new UserModelFake(
            ['email' => 'user@user.ru', "password" => "12345", "id" => 0, "auth_token" => "token"]
        ), $this->tokenGuard->user());
    }

    /** @test */
    public function userWithAuthTokenInRequestHeader(): void
    {
        $_SERVER["HTTP_AUTHORIZATION"] = "Bearer token";

        $this->assertEquals(new UserModelFake(
            ['email' => 'user@user.ru', "password" => "12345", "id" => 0, "auth_token" => "token"]
        ), $this->tokenGuard->user());
    }

    /** @test */
    public function tokenFromRequestHeader(): void
    {
        $_SERVER["HTTP_AUTHORIZATION"] = "Bearer token";

        $this->assertEquals($this->tokenGuard->getTokenForRequest(), "token");
    }

    /** @test */
    public function tokenFromRequest(): void
    {
        $_REQUEST["auth_token"] = "token";

        $this->assertEquals($this->tokenGuard->getTokenForRequest(), "token");
    }

    /** @test */
    public function tokenFromRequestPost(): void
    {
        $_POST["auth_token"] = "token";

        $this->assertEquals($this->tokenGuard->getTokenForRequest(), "token");
    }

    /** @test */
    public function userWithoutRequestParamsToken(): void
    {
        $this->assertNull($this->tokenGuard->user());
    }

    protected function tearDown(): void
    {
        self::flushAll();
    }
}
