<?php

namespace Sinbadxiii\PhalconAuth\Middlewares;

/**
 * Interface Accessicate
 * @package Sinbadxiii\PhalconAuth\Middlewares
 */
interface Accessicate
{
    /**
     * @return bool
     */
   public function authAccess(): bool;
}

