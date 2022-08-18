<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

/**
 * Class User
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class User implements AuthenticatableInterface
{
    private int $id;
    private string $password;

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
     * @return mixed
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}