<?php

namespace Sinbadxiii\PhalconAuth\Tests\Adapter;

use Phalcon\Config\Config;
use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\Manager;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;

/**
 * Class AdapterTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class AdapterMemoryTest extends AbstractTestCase
{
    protected $manager;
    protected $security;
    protected $config;

    /** @test */
    public function itShouldReturnFromAdapterMemoryUser()
    {
        $this->config = new Config(require (__DIR__ . "/../../config/auth.php"));

        $this->config->auth->providers->users->adapter = "memory";
        $this->config->auth->providers->users->model = UserModelFake::class;

        $data = [
            0 => ["id" => 0, "name" => "user", "password" => "54325"],
            1 => ["id" => 1, "name" => "user2", "password" => "54225"],
            2 => ["id" => 2, "name" => "user3", "password" => "34225"],
        ];

        $this->config->auth->providers->users->data = $data;

        $this->security = new Security();
        $this->manager = new Manager(
            $this->config->auth, $this->security
        );
        $providerAdapterMemory = $this->manager->getAdapterProvider("users");

        $this->assertEquals(new UserModelFake($data[0]), $providerAdapterMemory->retrieveById(0));
        $this->assertEquals(new UserModelFake($data[1]), $providerAdapterMemory->retrieveByCredentials(["name" => "user2"]));
        $this->assertEquals((new UserModelFake($data[2]))->getId(), 2);
    }
}
