<?php

namespace Sinbadxiii\PhalconAuth\Tests\Access;

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;
use Sinbadxiii\PhalconAuth\Access\AccessInterface;
use Sinbadxiii\PhalconAuth\Access\Guest;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class GuestAccessTest
 * @package Sinbadxiii\PhalconAuth\Tests\Access
 */
class GuestAccessTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementGuestAccess(): void
    {
        $gustAccess = new Guest();

        $this->assertInstanceOf(AccessInterface::class, $gustAccess);
        $this->assertInstanceOf(AbstractAccess::class, $gustAccess);
    }

    /**
     * @test
     */
    public function allowedIfTrueGuestAccess(): void
    {
        $gustAccessMock =  $this->createMock(Guest::class);
        $gustAccessMock->method("allowedIf")->willReturn(true);


        $this->assertTrue($gustAccessMock->allowedIf());
    }

    /**
     * @test
     */
    public function allowedIfFalseGuestAccess(): void
    {
        $gustAccessMock =  $this->createMock(Guest::class);
        $gustAccessMock->method("allowedIf")->willReturn(false);


        $this->assertFalse($gustAccessMock->allowedIf());
    }
}