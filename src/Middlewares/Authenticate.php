<?php

namespace Sinbadxiii\PhalconAuth\Middlewares;

use Phalcon\Di\Injectable;

class Authenticate extends Injectable
{
    /**
     * @description custom controller property
     * for disable auth check into controller
     */
    protected $guest = false;

    public function beforeExecuteRoute($event, $dispatcher)
    {
        if ($this->auth->check() || $this->isGuest()) {
            return true;
        }

        $this->unauthenticated();
    }

    protected function unauthenticated()
    {
        $this->redirectTo();
    }

    protected function redirectTo()
    {
        //custom url
    }

    protected function setGuest($guest)
    {
        $this->guest = $guest;
    }

    protected function isGuest()
    {
        return $this->guest;
    }
}

