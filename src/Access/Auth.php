<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

/**
 * Class Auth
 * @package Sinbadxiii\PhalconAuth\Access
 */
class Auth extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->check()) {
            return true;
        }

        return false;
    }
}