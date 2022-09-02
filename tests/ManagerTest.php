<?php

namespace Sinbadxiii\PhalconAuth\Tests;

use InvalidArgumentException;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Manager as SessionManager;
use Sinbadxiii\PhalconAuth\Access\Auth;
use Sinbadxiii\PhalconAuth\Access\Guest;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Guard\Session;
use Sinbadxiii\PhalconAuth\Guard\Token;
use Sinbadxiii\PhalconAuth\Manager;
use Sinbadxiii\PhalconAuth\ManagerInterface;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;

/**
 * Class ManagerTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class ManagerTest extends AbstractTestCase
{
    protected $manager;
    protected $guard;

    protected function setUp(): void
    {
        $security = new Security();
        $adapter  = new Memory($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setData([]);

        $guard = new Session(
            $adapter,
            new SessionManager(),
            new Cookies(),
            new Request(),
            new EventsManager(),
        );

        $manager = new Manager();
        $manager->addGuard("web", $guard);

        $this->manager = $manager;
        $this->guard = $guard;
    }

    /**
     * @test
     */
    public function implementFromManagerInterface(): void
    {
        $this->assertInstanceOf(ManagerInterface::class, $this->manager);
    }

    /**
     * @test
     */
    public function guard(): void
    {
        $this->assertEquals($this->guard, $this->manager->guard("web"));
    }

    /**
     * @test
     */
    public function getDefaultGuardFromAddGuardArgument(): void
    {
        $this->manager->addGuard("web", $this->guard, true);

        $this->assertEquals($this->guard, $this->manager->getDefaultGuard());
    }

    /**
     * @test
     */
    public function getDefaultGuard(): void
    {
        $this->manager->setDefaultGuard($this->guard);

        $this->assertEquals($this->guard, $this->manager->getDefaultGuard());
    }

    /**
     * @test
     */
    public function multipleAddGuard(): void
    {
        $security = new Security();
        $adapter  = new Memory($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setData([]);

        $guard = new Session(
            $adapter,
            new SessionManager(),
            new Cookies(),
            new Request(),
            new EventsManager(),
        );

        $guardToken = new Token(
            $adapter,
            [
                "inputKey" => "token",
                "storageKey" => "token",
            ],
            new Request()
        );

        $manager = new Manager();
        $manager->addGuard("web", $guard);
        $manager->addGuard("api", $guardToken, true);

        $this->assertEquals($guard, $manager->guard("web"));
        $this->assertEquals($guardToken, $manager->guard("api"));
        $this->assertEquals($guardToken, $manager->getDefaultGuard());
    }

    /**
     * @test
     */
    public function setAccess(): void
    {
        $this->manager->setAccess(new Auth());

        $this->assertEquals(new Auth(), $this->manager->getAccess());
    }

    /**
     * @test
     */
    public function setAccessAuth(): void
    {
        $this->manager->setAccessList(
            [
                "auth"  => Auth::class,
                'guest' => Guest::class
            ]
        );
        $this->manager->access("auth");
        $this->assertEquals(new Auth(), $this->manager->getAccess());
    }

    /**
     * @test
     */
    public function setAccessGuest(): void
    {
        $this->manager->setAccessList(
            [
                "auth"  => Auth::class,
                'guest' => Guest::class
            ]
        );
        $this->manager->access("guest");
        $this->assertEquals(new Guest(), $this->manager->getAccess());
    }

    /**
     * @test
     */
    public function setAccessNotIncluded(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->manager->setAccessList(
            [
                "auth"  => Auth::class,
                'guest' => Guest::class
            ]
        );
        $this->manager->access("admin");
    }

    /**
     * @test
     */
    public function exceptActions(): void
    {
        $this->manager->setAccessList(
            [
                "auth"  => Auth::class
            ]
        );
        $this->manager->access("auth")->except("action", "action2");

        $this->assertEquals(["action", "action2"], $this->manager->getAccess()->getExceptActions());
    }

    /**
     * @test
     */
    public function onlyActions(): void
    {
        $this->manager->setAccessList(
            [
                "auth"  => Auth::class
            ]
        );
        $this->manager->access("auth")->only("action");

        $this->assertEquals(["action"], $this->manager->getAccess()->getOnlyActions());
    }

    /**
     * @test
     */
    public function guardByDefault(): void
    {
        $security = new Security();
        $adapter  = new Memory($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setData([]);

        $guardToken = new Token(
            $adapter,
            [
                "inputKey" => "token",
                "storageKey" => "token",
            ],
            new Request()
        );

        $manager = new Manager();
        $manager->addGuard("api", $guardToken, true);

        $this->assertEquals($manager->guard(), $manager->getDefaultGuard());
    }
}
