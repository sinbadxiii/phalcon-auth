<?php

namespace Sinbadxiii\PhalconAuth\Tests;

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
use Sinbadxiii\PhalconAuth\ManagerFactory;
use Sinbadxiii\PhalconAuth\ManagerInterface;

/**
 * Class ManagerTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class ManagerFactoryTest extends AbstractTestCase
{
    protected $manager;
    protected $security;
    protected $config;

    protected function setUp(): void
    {
        $this->config = new Config(require (__DIR__ . "/../config/auth.php"));
        $this->security = new Security();
        $this->manager = new ManagerFactory(
            $this->config->auth->toArray(),
            $this->security,
            new SessionManager(),
            new Cookies(),
            new Request(),
            new EventsManager(),

        );
    }

    /**
     * @test
     */
    public function implementFromManagerInterface(): void
    {
        $this->assertInstanceOf(ManagerInterface::class, $this->manager);
    }

    /** @test */
    public function itShouldReturnMatchingDefaultGuardFromConfig()
    {
        $this::assertEquals(
            $this->config->auth->defaults->guard, $this->manager->getDefaultGuardName()
        );
    }

    /** @test */
    public function itShouldReturnMatchingModelAdapterProviderByConfig()
    {
        $this::assertEquals(
            new Model($this->security, $this->config->auth->providers->users->toArray()), $this->manager->getAdapterProvider("users")
        );
    }

    /** @test */
    public function itShouldReturnMatchingStreamAdapterProviderByConfig()
    {
        $this->config->auth->providers->users->adapter = "stream";

        $this->manager = new ManagerFactory(
            $this->config->auth->toArray(),
            $this->security,
            new SessionManager(),
            new Cookies(),
            new Request(),
            new EventsManager(),

        );

        $this::assertEquals(
            new Stream($this->security, $this->config->auth->providers->users->toArray()), $this->manager->getAdapterProvider("users")
        );
    }

    /** @test */
    public function itShouldReturnMatchingMemoryAdapterProviderByConfig()
    {
        $this->config->auth->providers->users->adapter = "memory";

        $this->manager = new ManagerFactory(
            $this->config->auth->toArray(),
            $this->security,
            new SessionManager(),
            new Cookies(),
            new Request(),
            new EventsManager(),

        );

        $this::assertEquals(
            new Memory($this->security, $this->config->auth->providers->users->toArray()), $this->manager->getAdapterProvider("users")
        );
    }

    /** @test */
    public function itShouldReturnSetAuthAccess()
    {
        $authAccess = new Auth();
        $this->manager->setAccess($authAccess);

        $this::assertEquals(
            $authAccess, $this->manager->getAccess()
        );
    }

    /** @test */
    public function itShouldReturnSetGuestAccess()
    {
        $guestAccess = new Guest();
        $this->manager->setAccess($guestAccess);

        $this::assertEquals(
            $guestAccess, $this->manager->getAccess()
        );
    }

    /** @test */
    public function itShouldReturnAccess()
    {
        $this->manager->setAccessList(
            [
                'auth'  => Auth::class,
                'guest' => Guest::class,
            ]
        );

        $this::assertEquals(
            new Auth(), $this->manager->access("auth")->getAccess()
        );
        $this::assertEquals(
            new Guest(), $this->manager->access("guest")->getAccess()
        );
    }

    /** @test */
    public function itShouldReturnExceptionNotIncludeAccess()
    {
        $this::expectException(\InvalidArgumentException::class);

        $this->manager->setAccessList(
            [
                'auth'  => Auth::class,
                'guest' => Guest::class
            ]
        );

        $this->manager->access("admin");
    }
}
