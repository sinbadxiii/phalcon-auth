<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Users;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;

/**
 * Class User
 * @package Sinbadxiii\PhalconAuth\Users
 */
class User implements AuthenticatableInterface
{
    private $id;
    private $key;
    private $password;

    /**
     * User constructor.
     * @param $data
     */
    public function __construct($data)
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }

    /**
     * @param $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getKey()};
    }

    /**
     * @return mixed
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    private function getKey()
    {
        return "id";
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}