<?php

namespace Sinbadxiii\PhalconAuth\Guard;

interface BasicAuthInterface
{
    public function basic($field = 'email', $extraConditions = []);
    public function onceBasic($field = 'email', $extraConditions = []);
}