<?php

namespace Sinbadxiii\PhalconAuth\Middlewares;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Dispatcher;

class Authenticate extends Injectable implements AuthenticatesRequest
{
    /**
     * @var
     */
    protected Dispatcher $dispatcher;

    /**
     * @param $event
     * @param $dispatcher
     */
    public function beforeDispatch($event, $dispatcher)
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
