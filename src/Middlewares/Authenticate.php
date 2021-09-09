<?php

namespace Sinbadxiii\PhalconAuth\Middlewares;

use Phalcon\Di\Injectable;

class Authenticate extends Injectable implements AuthenticatesRequest
{
    protected $dispatcher;

    public function beforeExecuteRoute($event, $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        $this->authenticate();
    }

    /**
     * @return bool
     */
    protected function authenticate()
    {
        if ($this->auth->authenticate() || $this->isGuest()) {
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
        $controller = $this->dispatcher->getControllerClass();

        return !(new $controller)->authAccess();
    }
}
