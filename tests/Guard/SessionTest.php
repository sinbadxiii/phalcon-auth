<?php

namespace Sinbadxiii\PhalconAuth\Tests\Guard;

use Phalcon\Encryption\Security;
use Phalcon\Events\AbstractEventsAware;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Manager as SessionManager;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Guard\BasicAuthInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardStatefulInterface;
use Sinbadxiii\PhalconAuth\Guard\Session;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;
use function sha1;

/**
 * Class SessionTest
 * @package Sinbadxiii\PhalconAuth\Tests\Adapter
 */
class SessionTest extends AbstractTestCase
{
    protected $sessionGuard;
    protected $adapter;

    protected function setUp(): void
    {
        $security = new Security();

        $сonfigAdapter = [
                'data' => [
                    ['email' => 'user@user.ru', "password" => "12345", "id" => 0],
                    ['email' => 'user1@user1.ru', "password" => "1234", "id" => 1],
                ],
                'model' => UserModelFake::class
            ];

        $adapter  = new Memory($security, $сonfigAdapter);

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
    public function constructor(): void
    {
        $this->assertEquals("Sinbadxiii\PhalconAuth\Guard\Session", $this->sessionGuard::class);
    }

    /** @test */
    public function extendsFromAbstractEventsAware(): void
    {
        $this->assertInstanceOf(AbstractEventsAware::class, $this->sessionGuard);
    }

    /** @test */
    public function implementFromGuardInterface(): void
    {
        $this->assertInstanceOf(GuardInterface::class, $this->sessionGuard);
    }

    /** @test */
    public function implementFromGuardStatefulInterface(): void
    {
        $this->assertInstanceOf(GuardStatefulInterface::class, $this->sessionGuard);
    }

    /** @test */
    public function implementFromBasicAuthInterface(): void
    {
        $this->assertInstanceOf(BasicAuthInterface::class, $this->sessionGuard);
    }

    /** @test */
    public function successfulAttemptLogin(): void
    {
        $attempt = $this->sessionGuard->attempt([
            "email" => "user@user.ru", "password" => "12345"
        ]);

        $this->assertTrue($attempt);
    }

    /** @test */
    public function unsuccessfulAttemptLogin(): void
    {
        $attempt = $this->sessionGuard->attempt([
            "email" => "user@user.ru", "password" => "123456"
        ]);

        $this->assertFalse($attempt);
    }

    /** @test */
    public function successfulAttemptLoginWithUser(): void
    {
        $attempt = $this->sessionGuard->attempt([
            "email" => "user@user.ru", "password" => "12345"
        ]);

        $this->assertEquals(
            new UserModelFake(["email" => "user@user.ru", "password" => "12345", "id" => 0]),
            $this->sessionGuard->user()
        );
    }

    /** @test */
    public function successfulAttemptLoginWithValidateCredentials(): void
    {
        $validate = $this->sessionGuard->validate([
            "email" => "user@user.ru", "password" => "12345"
        ]);

        $this->assertTrue($validate);
    }

    /** @test */
    public function unsuccessfulAttemptLoginWithValidateCredentials(): void
    {
        $validate = $this->sessionGuard->validate([
            "email" => "user@user.ru", "password" => "123456"
        ]);

        $this->assertFalse($validate);
    }

    /** @test */
    public function gettingNameGuard(): void
    {
        $this->assertEquals(
            "auth-" . sha1(
                $this->sessionGuard::class . $this->sessionGuard->getAdapter()::class
            ),
            $this->sessionGuard->getName()
        );
    }

    /** @test */
    public function gettingRememberName(): void
    {
        $this->assertEquals(
            "remember_" . sha1(
                $this->sessionGuard::class . $this->sessionGuard->getAdapter()::class
            ),
            $this->sessionGuard->getRememberName()
        );
    }

    /** @test */
    public function successUserLogin(): void
    {
        $user = new UserModelFake(['email' => 'user@user.ru', "password" => "12345", "id" => 0]);

        $this->sessionGuard->login($user);

        $this->assertEquals($user, $this->sessionGuard->getUser());
    }

    /** @test */
    public function successUserLoginById(): void
    {
        $user = new UserModelFake(['email' => 'user1@user1.ru', "password" => "1234", "id" => 1]);

        $loggingUser = $this->sessionGuard->loginById(1);

        $this->assertEquals($user, $loggingUser);
    }

    /** @test */
    public function failUserLoginById(): void
    {
        $loggingUser = $this->sessionGuard->loginById(9999);

        $this->assertFalse($loggingUser);
    }

    /** @test */
    public function successOnceLogin(): void
    {
        $loggingUser = $this->sessionGuard->once(
            [
                "email" => "user@user.ru", "password" => '12345'
            ]
        );

        $this->assertTrue($loggingUser);
    }

    /** @test */
    public function failOnceLogin(): void
    {
        $loggingUser = $this->sessionGuard->once(
            [
                "email" => "user999@user999.ru", "password" => '12345'
            ]
        );

        $this->assertFalse($loggingUser);
    }

    /** @test */
    public function logout(): void
    {
        $this->sessionGuard->attempt([
            "email" => "user@user.ru", "password" => "12345"
        ]);

        $this->assertEquals($this->sessionGuard->user(), $this->sessionGuard->getLastUserAttempted());
        $this->sessionGuard->logout();
        $this->assertNull($this->sessionGuard->user());
    }

    /** @test */
    public function gettingAdapterMemory(): void
    {
        $this->assertEquals($this->adapter, $this->sessionGuard->getAdapter());
    }

    /** @test */
    public function settingAdapterMemory(): void
    {
        $this->sessionGuard->setAdapter($this->adapter);
        $this->assertEquals($this->adapter, $this->sessionGuard->getAdapter());
    }

    protected function tearDown(): void
    {
        self::flushAll();
    }
}
