<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

interface AuthenticatesRequestInterface
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher): void;
    //protected function authenticate();
}