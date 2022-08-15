<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Di\Di;
use Phalcon\Support\Helper\Str\StartsWith;

use function is_null;

/**
 * Class Token
 * @package Sinbadxiii\PhalconAuth\Guard
 */
class Token implements GuardInterface
{
    use GuardHelper;

    protected string $name;
    protected $eventsManager;
    protected $request;

    /**
     * The name of the query string item from the request.
     *
     * @var string
     */
    protected string $inputKey;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $storageKey;

    protected $provider;

    public function __construct($name, $provider, $inputKey = 'auth_token', $storageKey = 'auth_token')
    {
        $this->name     = $name;
        $this->provider = $provider;
        $this->eventsManager  = Di::getDefault()->getShared("eventsManager");
        $this->request        = Di::getDefault()->getShared("request");
        $this->inputKey   = $inputKey;
        $this->storageKey = $storageKey;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if ( ! empty($token)) {
            $user = $this->provider->retrieveByCredentials([
                $this->storageKey => $token,
            ]);
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }

    public function getTokenForRequest()
    {
        $token = $this->request->get($this->inputKey);

        if (empty($token)) {
            $token = $this->bearerToken();
        }

        return $token;
    }

    private function bearerToken()
    {
        $header = $this->request->getHeader('Authorization');
        $object = new StartsWith();

        if ($object($header, 'Bearer ')) {
            return mb_substr($header, 7, null, 'UTF-8');
        }
    }
}