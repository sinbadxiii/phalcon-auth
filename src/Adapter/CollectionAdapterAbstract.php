<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Phalcon\Config\ConfigInterface;
use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

/**
 * Class CollectionAdapterAbstract
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
abstract class CollectionAdapterAbstract implements AdapterInterface
{
    protected Security $hasher;
    protected $config;

    /**
     * @param $hasher
     * @param $config
     */
    public function __construct(Security $hasher, ConfigInterface $config)
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
        $collection = new UserCollection($this->getData());

        return $collection->first($credentials);
    }

    /**
     * @param $identifier
     * @return User|null
     */
    public function retrieveById($identifier): ?User
    {
        $collection = new UserCollection($this->getData());

        return ($userData = $collection[$identifier]) ? new User($userData) : null;
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