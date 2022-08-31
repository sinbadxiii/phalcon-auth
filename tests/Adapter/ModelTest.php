<?php

namespace Sinbadxiii\PhalconAuth\Tests\Adapter;

use Phalcon\Encryption\Security;
use ReflectionClass;
use Sinbadxiii\PhalconAuth\Adapter\AbstractAdapter;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;
use Sinbadxiii\PhalconAuth\Tests\User\UserModelFake;

/**
 * Class ModelTest
 * @package Sinbadxiii\PhalconAuth\Tests\Adapter
 */
class ModelTest extends AbstractTestCase
{
    protected $security;
    protected $config;

    /** @test */
    public function implementFromAdapterInterface(): void
    {
        $security = new Security();
        $adapter = new Model($security);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    /** @test */
    public function extendsFromAbstractAdapter(): void
    {
        $security = new Security();
        $adapter = new Model($security);
        $this->assertInstanceOf(AbstractAdapter::class, $adapter);
    }

    /** @test */
    public function getProviderStorageWithoutSetModel(): void
    {
        $this::expectException(\InvalidArgumentException::class);

        $security = new Security();
        $modelAdapter = new Model($security);

        $class = new ReflectionClass(Model::class);
        $method = $class->getMethod("getProviderStorage");
        $method->setAccessible(true);
        $method->invoke($modelAdapter);
    }

    /** @test */
    public function validateCredentials(): void
    {
        $security = new Security();
        $modelAdapter = new Model($security);

        $this->assertTrue($modelAdapter->validateCredentials(
            new UserModelFake(['password' => $security->hash('1234')]), ["password" => '1234']
        ));
    }

    /** @test */
    public function setModelEquals(): void
    {
        $security = new Security();
        $modelAdapter = new Model($security);

        $modelAdapter->setModel('Model');
        $this->assertEquals('Model', $modelAdapter->getModel());
    }

}
