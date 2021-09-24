<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface BasicAuth
{
    public function basic($field = 'email', $extraConditions = []);
    public function onceBasic($field = 'email', $extraConditions = []);
}