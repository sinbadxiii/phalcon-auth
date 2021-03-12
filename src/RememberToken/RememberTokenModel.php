<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\RememberToken;

use Phalcon\Di;
use Phalcon\Mvc\Model;

class RememberTokenModel extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $user_agent;

    /**
     * @var integer
     */
    public $created_at;

    /**
     * @var integer
     */
    public $updated_at;

    /**
     * @var integer
     */
    public $expired_at;

    public function initialize()
    {
        $configAuth = Di::getDefault()->getShared("config")->auth;

        $tableRememberToken = $configAuth->guards->{$configAuth->defaults->guard}->provider .
            "_remember_tokens";

        $this->nameTable = $tableRememberToken;

        $this->setSource($this->nameTable);
    }

    public function beforeValidationOnCreate()
    {
        $this->created_at = date(DATE_ATOM);
        $this->updated_at = date(DATE_ATOM);
        if (!$this->expired_at) {
            $this->expired_at = date(DATE_ATOM);
        }
    }

    public function beforeValidationOnSave()
    {
        if (!$this->created_at) {
            $this->created_at = date(DATE_ATOM);
        }
        if (!$this->expired_at) {
            $this->expired_at = date(DATE_ATOM);
        }
        $this->updated_at = date(DATE_ATOM);
    }

    public function beforeValidationOnUpdate()
    {
        $this->updated_at = date(DATE_ATOM);
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getUserAgent()
    {
        return $this->user_agent;
    }
}