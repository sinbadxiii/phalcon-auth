<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Di\Di;
use Phalcon\Http\Request;
use Phalcon\Support\Helper\Str\StartsWith;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
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
    protected $adapter;

    /**
     * @param AdapterInterface $adapter
     * @param array $config
     */
    public function __construct(AdapterInterface $adapter, array $config, Request $request)
    {
        $this->adapter       = $adapter;
        $this->request       = $request;
        $this->inputKey      = $config['inputKey'];
        $this->storageKey    = $config['storageKey'];
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
            $user = $this->adapter->retrieveByCredentials([
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

        if ($this->adapter->retrieveByCredentials($credentials)) {
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

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter): static
    {
        $this->adapter = $adapter;

        return $this;
    }
}