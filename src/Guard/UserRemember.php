<?php

namespace Sinbadxiii\PhalconAuth\Guard;

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
     * @throws \JsonException
     */
    public function __construct($data)
    {
        $this->data = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return mixed
     */
    public function id(): mixed
    {
        return $this->data->id;
    }

    /**
     * @return mixed
     */
    public function token(): mixed
    {
        return $this->data->token;
    }

    /**
     * @return mixed
     */
    public function userAgent(): mixed
    {
        return $this->data->user_agent;
    }
}

