<?php

namespace Sinbadxiii\PhalconAuth\Tests\Adapter;

use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\Adapter\AbstractAdapter;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AdapterTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class AbstractAdapterTest extends AbstractTestCase
{
    /** @test */
    public function implementFromAdapterInterface(): void
    {
        $security = new Security();
        $abstractAdapter = $this->getMockForAbstractClass(AbstractAdapter::class, [$security]);
        $this->assertInstanceOf(AdapterInterface::class, $abstractAdapter);
    }

    /** @test */
    public function setConfig(): void
    {
        $security = new Security();
        $abstractAdapter = $this->getMockForAbstractClass(AbstractAdapter::class, [$security]);
        $config = [
            'something' => 'Something'
        ];

        $abstractAdapter->setConfig($config);
        $this->assertEquals($config, $abstractAdapter->getConfig());
    }

    /** @test */
    public function getConfigWithoutValues(): void
    {
        $security = new Security();
        $abstractAdapter = $this->getMockForAbstractClass(AbstractAdapter::class, [$security]);
        $this->assertEquals([], $abstractAdapter->getConfig());
    }

    /** @test */
    public function setConfigWithModelValue(): void
    {
        $security = new Security();
        $abstractAdapter = $this->getMockForAbstractClass(AbstractAdapter::class, [$security]);
        $abstractAdapter->setConfig(
            [
                'model' => 'Model'
            ]
        );
        $this->assertEquals('Model', $abstractAdapter->getConfig()['model']);
    }

    /** @test */
    public function setModelEquals(): void
    {
        $security = new Security();
        $abstractAdapter = $this->getMockForAbstractClass(AbstractAdapter::class, [$security]);

        $abstractAdapter->setModel('Model');
        $this->assertEquals('Model', $abstractAdapter->getModel());
    }

    /** @test */
    public function setModelNotEquals(): void
    {
        $security = new Security();
        $abstractAdapter = $this->getMockForAbstractClass(AbstractAdapter::class, [$security]);

        $abstractAdapter->setModel('Model');
        $this->assertNotEquals('Model2', $abstractAdapter->getModel());
    }
}
