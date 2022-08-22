<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Phalcon\Config\ConfigInterface;
use Phalcon\Encryption\Security;
use Phalcon\Di\Di;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

/**
 * Class Model
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Model implements AdapterInterface, AdapterWithRememberTokenInterface
{
    /**
     * @var
     */
    protected $model;

    /**
     * @var
     */
    protected $hasher;

    /**
     * @param $hasher
     * @param $config
     */
    public function __construct(Security $hasher, ConfigInterface $config)
    {
        $this->hasher = $hasher;
        $this->model  = $config->model;
    }

    /**
     * @param array $credentials
     * @return mixed
     */
    public function retrieveByCredentials(array $credentials)
    {
        $builder = Di::getDefault()->get('modelsManager')
            ->createBuilder()
            ->from([$this->model]);

        foreach ($credentials as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            $builder->andWhere("{$key} = :{$key}:", [$key => $value]);
        }

        return $builder->getQuery()->execute()->getFirst();
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function retrieveById($identifier)
    {
        return $this->model::findFirst($identifier);
    }

    /**
     * @param $identifier
     * @param $token
     * @param $user_agent
     * @return void|null
     */
    public function retrieveByToken($identifier, $token, $user_agent)
    {
        $retrievedModel = $this->model::findFirst($identifier);

        if (!$retrievedModel) {
            return;
        }

        $rememberTokenModel = $retrievedModel->getRememberToken($token);

        if (!$rememberTokenModel) {
            return;
        }

        $rememberToken = $rememberTokenModel->getToken();

        return $rememberToken && hash_equals($rememberToken, $token) && hash_equals(
            $rememberTokenModel->getUserAgent(), $user_agent
        ) ? $retrievedModel : null;
    }

    /**
     * @param RememberingInterface $user
     * @return RememberTokenInterface
     */
    public function createRememberToken(RememberingInterface $user): RememberTokenInterface
    {
        return $user->createRememberToken();
    }

    /**
     * @param AuthenticatableInterface $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        return $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}