<?php

namespace Sinbadxiii\PhalconAuth\Tests\Access;

use Phalcon\Di\Injectable;
use Sinbadxiii\PhalconAuth\Access\AbstractAccess;
use Sinbadxiii\PhalconAuth\Access\AccessInterface;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AbstractAccessTest
 * @package Sinbadxiii\PhalconAuth\Tests\Access
 */
class AbstractAccessTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementAbstractAccess(): void
    {
        $abstractAccessMock = $this->getMockForAbstractClass(AbstractAccess::class);

        $this->assertInstanceOf(AccessInterface::class, $abstractAccessMock);
        $this->assertInstanceOf(Injectable::class, $abstractAccessMock);
    }

    /**
     * @test
     */
    public function exceptAbstractAccess(): void
    {
        $abstractAccessMock = $this->getMockForAbstractClass(AbstractAccess::class);
        $abstractAccessMock->setExceptActions("action", "action2", "action3");

        $this->assertEquals(["action", "action2", "action3"], $abstractAccessMock->getExceptActions());
    }

    /**
     * @test
     */
    public function onlyAbstractAccess(): void
    {
        $abstractAccessMock = $this->getMockForAbstractClass(AbstractAccess::class);
        $abstractAccessMock->setOnlyActions("action", "action2", "action3");

        $this->assertEquals(["action", "action2", "action3"], $abstractAccessMock->getOnlyActions());
    }

    /**
     * @test
     */
    public function isAllowedMethodAbstractAccessByDefaultFalse(): void
    {
        $abstractAccessMock = $this->getMockForAbstractClass(AbstractAccess::class);
        $isAllowed = $abstractAccessMock->isAllowed("action");

        $this->assertFalse($isAllowed);
    }

    /**
     * @test
     */
    public function isAllowedMethodAbstractAccessWithExceptAction(): void
    {
        $abstractAccessMock = $this->getMockForAbstractClass(AbstractAccess::class);
        $abstractAccessMock->setExceptActions("action");
        $isAllowed = $abstractAccessMock->isAllowed("action");

        $this->assertTrue($isAllowed);
    }

    /**
     * @test
     */
    public function isAllowedMethodAbstractAccessWithOnlyAction(): void
    {
        $abstractAccessMock = $this->getMockForAbstractClass(AbstractAccess::class);
        $abstractAccessMock->setOnlyActions("action");
        $isAllowed = $abstractAccessMock->isAllowed("action");

        $this->assertFalse($isAllowed);
    }
}