<?php

namespace Sinbadxiii\PhalconAuth\Tests\Adapter;

use Phalcon\Encryption\Security;
use ReflectionClass;
use Sinbadxiii\PhalconAuth\Adapter\AbstractAdapter;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Adapter\Stream;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;

/**
 * Class StreamTest
 * @package Sinbadxiii\PhalconAuth\Tests\Adapter
 */
class StreamTest extends AbstractTestCase
{
    /** @test */
    public function implementFromAdapterInterface(): void
    {
        $security = new Security();
        $adapter = new Stream($security);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    /** @test */
    public function extendsFromAbstractAdapter(): void
    {
        $security = new Security();
        $adapter = new Stream($security);
        $this->assertInstanceOf(AbstractAdapter::class, $adapter);
    }

    /** @test */
    public function itShouldReturnFromAdapterMemoryUser()
    {
        $security = new Security();
        $adapter = new Stream($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setFileSource(__DIR__ . "/users.json");

        $data = $adapter->getData();


        $this->assertEquals(new UserModelFake($data[0]), $adapter->findFirstById(0));
        $this->assertEquals(new UserModelFake($data[1]), $adapter->findFirstByCredentials(["name" => "user1"]));
        $this->assertEquals((new UserModelFake($data[2]))->getId(), 2);
    }

    /** @test */
    public function foundWithFirstFromData()
    {

        $security = new Security();
        $adapter = new Stream($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setFileSource(__DIR__ . "/users.json");

        $data = $adapter->getData();

        $this->assertEquals(new UserModelFake($data[1]), $adapter->first(
            $data,
            ["name" => "user1", "password" => "user1"]
        ));
    }

    /** @test */
    public function notFoundWithfirstFromData()
    {

        $security = new Security();
        $adapter = new Stream($security);
        $adapter->setModel(UserModelFake::class);
        $adapter->setFileSource(__DIR__ . "/users.json");

        $data = $adapter->getData();

        $this->assertNull($adapter->first(
            $data,
            ["name" => "user333", "password" => "user333"]
        ));
    }

    /** @test */
    public function getProviderStorageWithoutSetData(): void
    {
        $this::expectException(\InvalidArgumentException::class);

        $security = new Security();
        $streamAdapter = new Stream($security);

        $class = new ReflectionClass(Stream::class);
        $method = $class->getMethod("getProviderStorage");
        $method->setAccessible(true);
        $method->invoke($streamAdapter);
    }

    /** @test */
    public function getProviderStorageWithoutSetModel(): void
    {
        $this::expectException(\InvalidArgumentException::class);

        $security = new Security();
        $streamAdapter = new Stream($security);

        $class = new ReflectionClass(Stream::class);
        $method = $class->getMethod("getProviderStorage");
        $method->setAccessible(true);
        $method->invoke($streamAdapter);
    }

    /** @test */
    public function validateCredentials(): void
    {
        $security = new Security();
        $streamAdapter = new Stream($security);

        $this->assertTrue($streamAdapter->validateCredentials(
            new UserModelFake(['password' => '1234']), ["password" => '1234']
        ));
    }

    /** @test */
    public function setFileSource(): void
    {
        $security = new Security();
        $streamAdapter = new Stream($security);
        $streamAdapter->setFileSource(__DIR__ . "/users.json");

        $this->assertEquals(__DIR__ . "/users.json", $streamAdapter->getFileSource());
    }
}
