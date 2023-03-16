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
class Authenticate extends Injectable implements AuthenticatesRequestInterface
{
    /**
     * @var array
     */
    protected array $accessList = [];

    /**
     * @var string
     */
    protected string $actionName;

    /**
     * @return void
     */
    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher)
    {
        if (!empty($this->accessList)) {
            $this->getDI()->get("auth")->addAccessList($this->accessList);
        }
    }

    /**
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher): void
    {
        $this->actionName = $dispatcher->getActionName();

        $this->authenticate();
    }

    /**
     * @return bool|void
     */
    protected function authenticate()
    {
        if ($access = $this->getDI()->get("auth")->getAccess()) {

            if ($access->isAllowed($this->actionName)) {
                return true;
            }

            $access->redirectTo();
        }
    }
}
