<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

use Phalcon\Di\Di;

/**
 * Class Auth
 * @package Sinbadxiii\PhalconAuth\Access
 */
class Auth extends AbstractAccess
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if (Di::getDefault()->getShared("auth")->check()) {
            return true;
        }

        return false;
    }
}