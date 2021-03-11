<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Events;

class Logout extends EventAbstract
{
    protected $user;

    /**
     * Logout constructor.
     * @param $user
     */
    public function __construct($user) {
        $this->user = $user;
    }
}