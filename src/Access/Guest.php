<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

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
        if ($this->getDI()->get("auth")->guest()) {
            return true;
        }

        return false;
    }
}