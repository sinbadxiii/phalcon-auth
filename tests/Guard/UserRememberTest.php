<?php

namespace Sinbadxiii\PhalconAuth\Tests\Guard;

use JsonException;
use Sinbadxiii\PhalconAuth\Guard\UserRemember;
use Sinbadxiii\PhalconAuth\Tests\AbstractTestCase;

/**
 * Class UserRememberTest
 * @package Sinbadxiii\PhalconAuth\Tests\Adapter
 */
class UserRememberTest extends AbstractTestCase
{
    private CONST DATA = '{"id":"1","token":"123456","user_agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36"}';

    /** @test */
    public function constructor(): void
    {
        $user = new UserRemember(self::DATA);
        $this->assertEquals("123456", $user->token());
        $this->assertEquals("1", $user->id());
        $this->assertEquals("Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36", $user->userAgent());
    }

    /** @test */
    public function JsonExceptionNotValidData(): void
    {
        $this->expectException(JsonException::class);
        $user = new UserRemember("{OOPS}");
        $this->assertEquals("123456", $user->token());
    }
}
