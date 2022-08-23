<?php

namespace Sinbadxiii\PhalconAuth\Tests\Adapter;

use Phalcon\Config\Config;
use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Adapter\Stream;
use Sinbadxiii\PhalconAuth\Manager;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AdapterTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class AdapterTest extends AbstractTestCase
{
    protected $manager;
    protected $security;
    protected $config;

    protected function setUp(): void
    {
        $this->config = new Config(require (__DIR__ . "/../../config/auth.php"));
        $this->security = new Security();
        $this->manager = new Manager(
            $this->config->auth, $this->security
        );
    }

    /** @test */
    public function itShouldReturnAdapterModel()
    {
        $providerAdapterModel = $this->manager->getAdapterProvider("users");

        $this->assertEquals(
            new Model(new Security(), $this->config->auth->providers->users), $providerAdapterModel
        );
    }

    /** @test */
    public function itShouldReturnAdapterMemory()
    {
        $this->config->auth->providers->users->adapter = "memory";
        $this->security = new Security();
        $this->manager = new Manager(
            $this->config->auth, $this->security
        );
        $providerAdapterMemory = $this->manager->getAdapterProvider("users");

        $this->assertEquals(new Memory($this->security,
                $this->config->auth->providers->users
            ), $providerAdapterMemory
        );
    }

    public function itShouldReturnAdapterStream()
    {
        $this->config->auth->providers->users->adapter = "stream";
        $this->security = new Security();
        $this->manager = new Manager(
            $this->config->auth, $this->security
        );
        $providerAdapterStream = $this->manager->getAdapterProvider("users");

        $this->assertEquals(new Stream($this->security,
                $this->config->auth->providers->users
            ), $providerAdapterStream
        );
    }


}
