<?php

namespace Sinbadxiii\PhalconAuth\Tests;

use Phalcon\Config\Config;
use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\Access\Auth;
use Sinbadxiii\PhalconAuth\Access\Guest;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Adapter\Stream;
use Sinbadxiii\PhalconAuth\Manager;
use Sinbadxiii\PhalconAuth\ManagerInterface;

/**
 * Class AuthTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class ManagerTest extends AbstractTestCase
{
    protected $manager;
    protected $security;
    protected $config;

    protected function setUp(): void
    {
        $this->config = new Config(require (__DIR__ . "/../config/auth.php"));
        $this->security = new Security();
        $this->manager = new Manager(
            $this->config->auth, $this->security
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
            $this->config->auth->defaults->guard, $this->manager->getDefaultDriver()
        );
    }

    /** @test */
    public function itShouldReturnMatchingModelAdapterProviderByConfig()
    {
        $this::assertEquals(
            new Model($this->security, $this->config->auth->providers->users), $this->manager->getAdapterProvider("users")
        );
    }

    /** @test */
    public function itShouldReturnMatchingStreamAdapterProviderByConfig()
    {
        $this->config->auth->providers->users->adapter = "stream";

        $this::assertEquals(
            new Stream($this->security, $this->config->auth->providers->users), $this->manager->getAdapterProvider("users")
        );
    }

    /** @test */
    public function itShouldReturnMatchingMemoryAdapterProviderByConfig()
    {
        $this->config->auth->providers->users->adapter = "memory";

        $this::assertEquals(
            new Memory($this->security, $this->config->auth->providers->users), $this->manager->getAdapterProvider("users")
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
            new Auth(), $this->manager->access("auth")
        );
        $this::assertEquals(
            new Guest(), $this->manager->access("guest")
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
