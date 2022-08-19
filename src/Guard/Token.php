<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Di\Di;
use Phalcon\Support\Helper\Str\StartsWith;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

use function is_null;

/**
 * Class Token
 * @package Sinbadxiii\PhalconAuth\Guard
 */
class Token implements GuardInterface
{
    use GuardHelper;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var mixed
     */
    protected $eventsManager;

    /**
     * @var mixed
     */
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

    /**
     * @var
     */
    protected $provider;

    /**
     * @param $name
     * @param $provider
     * @param $inputKey
     * @param $storageKey
     */
    public function __construct($name, $provider, $config)
    {
        $this->name          = $name;
        $this->provider      = $provider;
        $this->eventsManager = Di::getDefault()->getShared("eventsManager");
        $this->request       = Di::getDefault()->getShared("request");
        $this->inputKey      = $config->inputKey ?? "auth_token";
        $this->storageKey    = $config->storageKey ?? "auth_token";
    }

    /**
     * @return AuthenticatableInterface | null
     */
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

    /**
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
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

    /**
     * @return string|null
     */
    public function getTokenForRequest(): ?string
    {
        $token = $this->request->get($this->inputKey);

        if (empty($token)) {
            $token = $this->bearerToken();
        }

        return $token;
    }

    /**
     * @return string|void
     */
    private function bearerToken()
    {
        $header = $this->request->getHeader('Authorization');
        $object = new StartsWith();

        if ($object($header, 'Bearer ')) {
            return mb_substr($header, 7, null, 'UTF-8');
        }
    }
}