<?php

namespace Sinbadxiii\PhalconAuth\Tests\Middlewares;

use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class AuthenticateTest
 * @package Sinbadxiii\PhalconAuth\Tests\Middlewares
 */
class AccessicateTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function implementController(): void
    {
        $this::assertEquals(true, (new ControllerStub())->authAccess());
    }
}