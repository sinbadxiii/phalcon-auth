<?php

namespace Sinbadxiii\PhalconAuth\Tests;

use InvalidArgumentException;
use Phalcon\Config\Config;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Manager as SessionManager;
use Sinbadxiii\PhalconAuth\Access\Auth;
use Sinbadxiii\PhalconAuth\Access\Guest;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Adapter\Stream;
use Sinbadxiii\PhalconAuth\Guard\Session;
use Sinbadxiii\PhalconAuth\Guard\Token;
use Sinbadxiii\PhalconAuth\Manager;
use Sinbadxiii\PhalconAuth\ManagerFactory;
use Sinbadxiii\PhalconAuth\ManagerInterface;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;
use function var_dump;

/**
 * Class ManagerTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class ManagerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementFromManagerInterface(): void
    {
        $manager = new Manager();

        $this->assertInstanceOf(ManagerInterface::class, $manager);
    }

    /**
     * @test
     */
    public function guard(): void
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

        $this->assertEquals($guard, $manager->guard("web"));
    }

    /**
     * @test
     */
    public function getDefaultGuardFromAddGuardArgument(): void
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
        $manager->addGuard("web", $guard, true);

        $this->assertEquals($guard, $manager->getDefaultGuard());
    }

    /**
     * @test
     */
    public function getDefaultGuard(): void
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
        $manager->setDefaultGuard($guard);

        $this->assertEquals($guard, $manager->getDefaultGuard());
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
        $manager->setAccess(new Auth());

        $this->assertEquals(new Auth(), $manager->getAccess());
    }

    /**
     * @test
     */
    public function setAccessAuth(): void
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
        $manager->setAccessList(
            [
                "auth"  => Auth::class,
                'guest' => Guest::class
            ]
        );
        $manager->access("auth");
        $this->assertEquals(new Auth(), $manager->getAccess());
    }

    /**
     * @test
     */
    public function setAccessGuest(): void
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
        $manager->setAccessList(
            [
                "auth"  => Auth::class,
                'guest' => Guest::class
            ]
        );
        $manager->access("guest");
        $this->assertEquals(new Guest(), $manager->getAccess());
    }

    /**
     * @test
     */
    public function setAccessNotIncluded(): void
    {
        $this->expectException(InvalidArgumentException::class);

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
        $manager->setAccessList(
            [
                "auth"  => Auth::class,
                'guest' => Guest::class
            ]
        );
        $manager->access("admin");
    }

    /**
     * @test
     */
    public function exceptActions(): void
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
        $manager->setAccessList(
            [
                "auth"  => Auth::class
            ]
        );
        $manager->access("auth")->except("action", "action2");

        $this->assertEquals(["action", "action2"], $manager->getAccess()->getExceptActions());
    }

    /**
     * @test
     */
    public function onlyActions(): void
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
        $manager->setAccessList(
            [
                "auth"  => Auth::class
            ]
        );
        $manager->access("auth")->only("action");

        $this->assertEquals(["action"], $manager->getAccess()->getOnlyActions());
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
