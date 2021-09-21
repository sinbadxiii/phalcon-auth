<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guards;

use Phalcon\Helper\Str;
use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuth\Exceptions\UnauthorizedHttpException;

/**
 * Trait BasicHelper
 * @package Sinbadxiii\PhalconAuth\Guards
 */
trait BasicHelper
{
    /**
     * @param string $field
     * @param array $extraConditions
     * @return bool|UnauthorizedHttpException
     * @throws UnauthorizedHttpException
     */
    public function basic($field = 'email', $extraConditions = [])
    {
        if ($this->check()) {
            return true;
        }

        if ($this->attemptBasic($this->getRequest(), $field, $extraConditions)) {
            return true;
        }

        $this->failedBasicResponse();
    }

    /**
     * @param Request $request
     * @param $field
     * @param array $extraConditions
     * @return bool
     */
    protected function attemptBasic(Request $request, $field, $extraConditions = [])
    {
        if (! $request->getBasicAuth()) {
            return false;
        }

        return $this->attempt(array_merge(
            $this->basicCredentials($request, $field), $extraConditions
        ));
    }

    /**
     * @return UnauthorizedHttpException
     * @throws UnauthorizedHttpException
     */
    protected function failedBasicResponse(): UnauthorizedHttpException
    {
       throw new UnauthorizedHttpException('Basic: Invalid credentials.');
    }

    /**
     * @param Request $request
     * @param $field
     * @return array
     */
    protected function basicCredentials(Request $request, $field)
    {
        return [$field => $this->userFromBasic($request), 'password' => $this->passwordFromBasic($request)];
    }

    /**
     * @param string $field
     * @param array $extraConditions
     * @return mixed
     * @throws UnauthorizedHttpException
     */
    public function onceBasic($field = 'email', $extraConditions = [])
    {
        $credentials = $this->basicCredentials($this->getRequest(), $field);

        if ($this->once(array_merge($credentials, $extraConditions))) {
            return $this->getUser();
        }

        $this->failedBasicResponse();
    }

    /**
     * @param $request
     * @return mixed|string
     */
    private function userFromBasic(Request $request)
    {
        return $request->getBasicAuth()['username'];
    }

    /**
     * @param $request
     * @return mixed|string
     */
    private function passwordFromBasic($request)
    {
        return $request->getBasicAuth()['password'];
    }
}
