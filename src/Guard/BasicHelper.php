<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuth\Exception\UnauthorizedHttpException;

/**
 * Trait BasicHelper
 * @package Sinbadxiii\PhalconAuth\Guard
 */
trait BasicHelper
{
    /**
     * @param $field
     * @param $extraConditions
     * @return mixed
     * @throws \Sinbadxiii\PhalconAuth\Exception\UnauthorizedHttpException
     */
    public function basic($field = 'email', $extraConditions = []): mixed
    {
        if ($this->check()) {
            return true;
        }

        if ($this->attemptBasic($this->getRequest(), $field, $extraConditions)) {
            return true;
        }

        return false;
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
     * @param \Phalcon\Http\Request $request
     * @param $field
     * @return array
     */
    protected function basicCredentials(Request $request, $field): array
    {
        return [$field => $this->userFromBasic($request), 'password' => $this->passwordFromBasic($request)];
    }

    /**
     * @param $field
     * @param $extraConditions
     * @return mixed
     */
    public function onceBasic($field = 'email', $extraConditions = []): mixed
    {
        $credentials = $this->basicCredentials($this->getRequest(), $field);

        if ($this->once(array_merge($credentials, $extraConditions))) {
            return $this->getUser();
        }

        return false;
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
     * @return mixed
     */
    private function passwordFromBasic($request)
    {
        return $request->getBasicAuth()['password'];
    }
}
