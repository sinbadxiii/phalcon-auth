<?php
/****************************************************************
 * @project phalcon-auth
 * @file Logout.php
 * @date 3/3/2023
 * @author Jeremy <jeremy@christianpost.com>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * Copyright © Kenosis Media, Inc. All rights reserved.
 *****************************************************************/

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Events;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

/**
 * Class Logout
 * @package Sinbadxiii\PhalconAuth\Events
 */
class Logout
{
    private AuthenticatableInterface $user;

    public static function beforeLogout($user)
    {

    }

    public static function afterLogout($user)
    {

    }
}