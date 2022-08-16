<?php

namespace Sinbadxiii\PhalconAuth\Access;

/**
 * Class Guest
 * @package Sinbadxiii\PhalconAuth\Access
 */
class Guest extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->guest()) {
            return true;
        }

        return false;
    }
}