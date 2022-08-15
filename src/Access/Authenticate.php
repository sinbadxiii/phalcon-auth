<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * Class Authenticate
 * @package Sinbadxiii\PhalconAuth\Access
 */
class Authenticate extends Injectable implements AuthenticatesRequest
{
    /**
     * @var array
     */
    protected array $accessList = [];

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;


    public function __construct()
    {
        $this->auth->addAccessList($this->accessList);
    }

    /**
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;

        $this->authenticate();
    }

    /**
     * @return bool|void
     */
    protected function authenticate()
    {
        if ($access = $this->auth->getAccess()) {

            if ($access->isAllowed()) {
                return true;
            }

            $access->redirectTo();
        }
    }
}
