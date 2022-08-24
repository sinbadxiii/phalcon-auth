<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

use Phalcon\Di\Di;

/**
 * Class Guest
 * @package Sinbadxiii\PhalconAuth\Access
 */
class Guest extends AbstractAccess
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if (Di::getDefault()->getShared("auth")->guest()) {
            return true;
        }

        return false;
    }
}