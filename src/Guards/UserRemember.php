<?php

namespace Sinbadxiii\PhalconAuth\Guards;

/**
 * Class UserRemember
 * @package Sinbadxiii\PhalconAuth\Guard
 */
class UserRemember
{
    /**
     * @var
     */
    protected $data;

    /**
     * UserRemember constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->data->id;
    }

    /**
     * @return mixed
     */
    public function token()
    {
        return $this->data->token;
    }

    /**
     * @return mixed
     */
    public function userAgent()
    {
        return $this->data->user_agent;
    }
}

