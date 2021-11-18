<?php

namespace Sinbadxiii\PhalconAuth\Tests;

use Phalcon\Config\Config;
use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\Auth;

/**
 * Class AuthTest
 * @package Sinbadxiii\PhalconAuth\Tests
 */
class AuthTest extends AbstractTestCase
{
    /** @test */
    public function itShouldReturnMatchingDefaultGuardFromConfig()
    {
        $configAuth = new Config(require (__DIR__ . "/../config/auth.php"));

        $auth = new Auth(
            $configAuth->auth, new Security()
        );

        $this::assertEquals(
            $configAuth->auth->defaults->guard, $auth->getDefaultDriver()
        );
    }
}
