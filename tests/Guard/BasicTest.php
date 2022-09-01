<?php

namespace Sinbadxiii\PhalconAuth\Tests\Guard;

use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Manager as SessionManager;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Guard\Session;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;

use function base64_encode;

/**
 * Class SessionTest
 * @package Sinbadxiii\PhalconAuth\Tests\Adapter
 */
class BasicTest extends AbstractTestCase
{
    protected $sessionGuard;
    protected $adapter;

    protected function setUp(): void
    {
        $security = new Security();

        $configAdapter = [
                'data' => [
                    ['email' => 'user@user.ru', "password" => "12345", "id" => 0],
                    ['email' => 'user1@user1.ru', "password" => "1234", "id" => 1],
                ],
                'model' => UserModelFake::class
            ];

        $adapter  = new Memory($security, $configAdapter);

        $session = new Session(
            $adapter,
            new SessionManager(),
            new Cookies(),
            new Request(),
            new EventsManager(),
        );

        $this->sessionGuard = $session;
        $this->adapter      = $adapter;
    }

    /** @test */
    public function successfulAttemptLogin(): void
    {
        $_SERVER["PHP_AUTH_USER"] = "user1@user1.ru";
        $_SERVER["PHP_AUTH_PW"] = "1234";

        $attempt = $this->sessionGuard->basic("email");

        $this->assertTrue($attempt);
    }

    /** @test */
    public function successfulAttemptOnceLogin(): void
    {
        $_SERVER["PHP_AUTH_USER"] = "user1@user1.ru";
        $_SERVER["PHP_AUTH_PW"] = "1234";

        $logginedUser = $this->sessionGuard->onceBasic("email");

        $this->assertEquals($logginedUser, new UserModelFake(
            ['email' => 'user1@user1.ru', "password" => "1234", "id" => 1]
        ));
    }

    /** @test */
    public function unsuccessfulAttemptLoginWithWrongCredentials(): void
    {
        $_SERVER["PHP_AUTH_USER"] = "user99999@user9999.ru";
        $_SERVER["PHP_AUTH_PW"] = "1234";

        $attempt = $this->sessionGuard->basic("email");

        $this->assertFalse($attempt);
    }

    /** @test */
    public function unsuccessfulAttemptLoginWithEmptyCreadentials(): void
    {
        $attempt = $this->sessionGuard->basic("email");

        $this->assertFalse($attempt);
    }

    protected function tearDown(): void
    {
        self::flushAll();
    }
}
