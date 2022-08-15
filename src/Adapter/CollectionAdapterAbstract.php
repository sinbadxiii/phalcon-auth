<?php

namespace Sinbadxiii\PhalconAuth\Adapter;

use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use function var_dump;

/**
 * Class CollectionAdapterAbstract
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
abstract class CollectionAdapterAbstract implements AdapterInterface
{
    protected UserCollection $collection;
    protected $config;
    protected Security $hasher;

    /**
     * @param $hasher
     * @param $config
     */
    public function __construct($hasher, $config)
    {
        $this->hasher     = $hasher;
        $this->config     = $config;
    }

    /**
     * @return mixed
     */
    abstract protected function getData();

    /**
     * @param array $credentials
     * @return User|null
     */
    public function retrieveByCredentials(array $credentials): ?User
    {
        $this->collection = new UserCollection($this->getData());

        return $this->collection::first($credentials);
    }

    /**
     * @param $identifier
     * @return User|null
     */
    public function retrieveById($identifier): ?User
    {
        $this->collection = new UserCollection($this->getData());

        return ($userData = $this->collection[$identifier]) ? new User($userData) : null;
    }

    /**
     * @param AuthenticatableInterface $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        return ($this->config->passsword_crypted) ? $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        ) : $credentials['password'] === $user->getAuthPassword();
    }
}