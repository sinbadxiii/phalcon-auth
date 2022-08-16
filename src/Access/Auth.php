<?php

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