<?php

namespace Sinbadxiii\PhalconAuth\Tests\Adapter;

use Phalcon\Encryption\Security;
use ReflectionClass;
use Sinbadxiii\PhalconAuth\Adapter\AbstractAdapter;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Adapter\Memory;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;

/**
 * Class MemoryTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class MemoryTest extends AbstractTestCase
{
    protected $security;
    protected $config;

    /** @test */
    public function implementFromAdapterInterface(): void
    {
        $security = new Security();
        $adapter = new Memory($security);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    /** @test */
    public function extendsFromAbstractAdapter(): void
    {
        $security = new Security();
        $adapter = new Memory($security);
        $this->assertInstanceOf(AbstractAdapter::class, $adapter);
    }

    /** @test */
    public function itShouldReturnFromAdapterMemoryUser()
    {
        $data = [
            0 => ["id" => 0, "name" => "user", "password" => "54325"],
            1 => ["id" => 1, "name" => "user2", "password" => "54225"],
            2 => ["id" => 2, "name" => "user3", "password" => "34225"],
        ];

        $security = new Security();
        $adapter = new Memory($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setData($data);

        $this->assertEquals(new UserModelFake($data[0]), $adapter->retrieveById(0));
        $this->assertEquals(new UserModelFake($data[1]), $adapter->retrieveByCredentials(["name" => "user2"]));
        $this->assertEquals((new UserModelFake($data[2]))->getId(), 2);
    }

    /** @test */
    public function firstFromData()
    {
        $data = [
            0 => ["id" => 0, "name" => "user", "password" => "54325"],
            1 => ["id" => 1, "name" => "user2", "password" => "54225"],
            2 => ["id" => 2, "name" => "user3", "password" => "34225"],
        ];

        $security = new Security();
        $adapter = new Memory($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setData($data);
        $providerStorage = $adapter->getData();
        $this->assertEquals(new UserModelFake($data[1]), $adapter->first(
            $providerStorage,
            ["id" => 1, "name" => "user2", "password" => "54225"]
        ));
    }

    /** @test */
    public function getProviderStorageWithoutSetData(): void
    {
        $this::expectException(\InvalidArgumentException::class);

        $security = new Security();
        $memoryAdapter = new Memory($security);

        $class = new ReflectionClass(Memory::class);
        $method = $class->getMethod("getProviderStorage");
        $method->setAccessible(true);
        $method->invoke($memoryAdapter);
    }

    /** @test */
    public function getProviderStorageWithoutSetModel(): void
    {
        $this::expectException(\InvalidArgumentException::class);

        $security = new Security();
        $memoryAdapter = new Memory($security);

        $class = new ReflectionClass(Memory::class);
        $method = $class->getMethod("getProviderStorage");
        $method->setAccessible(true);
        $method->invoke($memoryAdapter);
    }

    /** @test */
    public function validateCredentials(): void
    {
        $security = new Security();
        $memoryAdapter = new Memory($security);

        $this->assertTrue($memoryAdapter->validateCredentials(
            new UserModelFake(['password' => '1234']), ["password" => '1234']
        ));
    }
}
