<?php

namespace Sinbadxiii\PhalconAuth\Providers;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\Providers\File\Parser;
use Sinbadxiii\PhalconAuth\Users\User;
use Sinbadxiii\PhalconAuth\Users\UsersC
ollection;

/**
 * Class UsersFileProvider
 * @package Sinbadxiii\PhalconAuth\Providers
 */
class FileProvider implements ProviderInterface
{
    protected UsersCollection $collection;
    private $config;
    protected $hasher;

    /**
     * UsersFileProvider constructor.
     * @param $hasher
     * @param $config
     * @throws \Sinbadxiii\PhalconAuth\Exceptions\Collection\JsonNotValidException
     * @throws \Sinbadxiii\PhalconAuth\Exceptions\Exception
     */
    public function __construct($hasher, $config)
    {
        $this->hasher     = $hasher;
        $this->config     = $config;
        $this->collection = Parser::file($config);
    }

    /**
     * @param array $credentials
     * @return mixed|User|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->collection::first($credentials);
    }

    /**
     * @param $identifier
     * @return User|null
     */
    public function retrieveById($identifier)
    {
        return ($data = $this->collection[$identifier]) ? new User($data) : null;
    }

    /**
     * @param AuthenticatableInterface $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials)
    {
        return ($this->config->passsword_crypted) ? $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        ) : $credentials['password'] === $user->getAuthPassword();
    }
}