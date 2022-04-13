<?php

namespace Sinbadxiii\PhalconAuth\Middlewares;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Dispatcher;

/**
 * Class Authenticate
 * @package Sinbadxiii\PhalconAuth\Middlewares
 */
class Authenticate extends Injectable implements AuthenticatesRequest
{
    private const PROPERTY_AUTH_ACCESS = "authAccess";

    /**
     * @var Dispatcher
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

        return !(new $controller)->authAccess() ||
            (property_exists($controller, self::PROPERTY_AUTH_ACCESS) &&
                (new $controller)->authAccess === false);
    }
}
