<?php

namespace Sinbadxiii\PhalconAuth\Providers;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberToken\RememberTokenModel;

class ModelProvider implements ProviderInterface
{
    protected $model;

    protected $hasher;

    protected $di;

    /**
     * UsersModelProvider constructor.
     * @param $hasher
     * @param $config
     */
    public function __construct($hasher, $config)
    {
        $this->hasher = $hasher;
        $this->model  = $config->model;
        $this->di = class_exists("\\Phalcon\\Di") ? new \Phalcon\Di : new \Phalcon\Di\Di;

    }

    public function retrieveByCredentials(array $credentials)
    {
        $builder = $this->di::getDefault()->get('modelsManager')
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

    public function retrieveById($identifier)
    {
        return $this->model::findFirst($identifier);

        /**
         * @todo придумать как не жестко привязывать к id
         */
//        return $this->di::getDefault()->get('modelsManager')
//            ->createBuilder()
//            ->from(['m' =>$this->model])
//            ->where("m.id = :id:",
//            [
//                "id" => $identifier
//            ])
//            ->getQuery()->execute()->getFirst();
    }

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

    public function createRememberToken(AuthenticatableInterface $user)
    {
        if (class_exists("\Phalcon\Security\Random")) {
            $random = new \Phalcon\Security\Random();
        }
        if (class_exists("\Phalcon\Encryption\Security\Random")) {
            $random = new \Phalcon\Encryption\Security\Random();
        }
        $token = $random->base64(60);

        $rememberToken = new RememberTokenModel();
        $rememberToken->token = $token;
        $rememberToken->user_agent = $this->di::getDefault()->get('request')->getUserAgent();
        $rememberToken->ip = $this->di::getDefault()->get('request')->getClientAddress();

        $user->setRememberToken($rememberToken);

        /**
         * @todo стоит ли тут вызывать ивент сохранения??
         */
        $user->save();

        return $rememberToken;
    }

    public function validateCredentials(AuthenticatableInterface $user, array $credentials)
    {
        return $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}