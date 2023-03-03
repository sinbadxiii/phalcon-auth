<?php
/****************************************************************
 * @project phalcon-auth
 * @file EventListener.php
 * @date 3/3/2023
 * @author Jeremy <jeremy@christianpost.com>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * Copyright © Kenosis Media, Inc. All rights reserved.
 *****************************************************************/

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Events;

use Phalcon\Events\EventInterface;
use Sinbadxiii\PhalconAuth\Events\Login;
use Sinbadxiii\PhalconAuth\Events\Logout;
use Sinbadxiii\PhalconAuth\Guard\GuardStatefulInterface;

/**
 * Class EventListener
 * @package Sinbadxiii\PhalconAuth\Events
 */
class EventListener
{
    public function beforeLogin(EventInterface $event, GuardStatefulInterface $guard, array $data = [])
    {
        return Login::beforeLogin();
    }

    public function afterLogin(EventInterface $event, GuardStatefulInterface $guard, array $data = [])
    {
        return Login::afterLogin($data['user']);
    }

    public function beforeLogout(EventInterface $event, GuardStatefulInterface $guard, array $data = [])
    {
        return Logout::BeforeLogout($data);
    }

    public function afterLogout(EventInterface $event, GuardStatefulInterface $guard, array $data = [])
    {
        return Logout::AfterLogout($event, $guard, $data);
    }
}