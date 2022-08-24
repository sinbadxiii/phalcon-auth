<?php

namespace Sinbadxiii\PhalconAuth\Tests\Access;

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;
use Sinbadxiii\PhalconAuth\Access\AccessInterface;
use Sinbadxiii\PhalconAuth\Access\Auth;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AuthAccessTest
 * @package Sinbadxiii\PhalconAuth\Tests\Access
 */
class AuthAccessTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementAuthAccess(): void
    {
        $authAccess = new Auth();

        $this->assertInstanceOf(AccessInterface::class, $authAccess);
        $this->assertInstanceOf(AbstractAccess::class, $authAccess);
    }

    /**
     * @test
     */
    public function allowedIfTrueAuthAccess(): void
    {
        $authAccessMock =  $this->createMock(Auth::class);
        $authAccessMock->method("allowedIf")->willReturn(true);


        $this->assertTrue($authAccessMock->allowedIf());
    }

    /**
     * @test
     */
    public function allowedIfFalseAuthAccess(): void
    {
        $authAccessMock =  $this->createMock(Auth::class);
        $authAccessMock->method("allowedIf")->willReturn(false);


        $this->assertFalse($authAccessMock->allowedIf());
    }

    /**
     * @test
     */
    public function isAllowedTrueWhenExceptActionAuthAccess(): void
    {
        $authAccess = new AuthStub();
        $authAccess->except("action");
        $authAccess->access = false;
        $isAllowed = $authAccess->isAllowed("action");

        $this->assertTrue($isAllowed);
    }

    /**
     * @test
     */
    public function isAllowedTrueWhenOnlyActionAuthAccess(): void
    {
        $authAccess = new AuthStub();
        $authAccess->only("action1");

        $authAccess->access = true;

        $isAllowed = $authAccess->isAllowed("action1");

        $this->assertTrue($isAllowed);
    }

    /**
     * @test
     */
    public function isAllowedFalseWhenOnlyActionAuthAccess(): void
    {
        $authAccess = new AuthStub();
        $authAccess->only("action1");

        $authAccess->access = false;

        $isAllowed = $authAccess->isAllowed("action1");

        $this->assertFalse($isAllowed);
    }
}