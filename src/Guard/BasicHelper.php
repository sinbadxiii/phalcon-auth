<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Http\Request;

/**
 * Trait BasicHelper
 * @package Sinbadxiii\PhalconAuth\Guard
 */
trait BasicHelper
{
    /**
     * @param $field
     * @param $extraConditions
     * @return bool
     */
    public function basic(string $field = 'email', array $extraConditions = []): bool
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
     * @param string $field
     * @param array $extraConditions
     * @return bool
     */
    protected function attemptBasic(Request $request, string $field, array $extraConditions = []): bool
    {
        if (! $request->getBasicAuth()) {
            return false;
        }

        return $this->attempt(array_merge(
            $this->basicCredentials($request, $field), $extraConditions
        ));
    }

    /**
     * @param Request $request
     * @param string $field
     * @return array
     */
    protected function basicCredentials(Request $request, string $field): array
    {
        return [
            $field => $this->userFromBasic($request),
            'password' => $this->passwordFromBasic($request)
        ];
    }

    /**
     * @param $field
     * @param $extraConditions
     * @return mixed
     */
    public function onceBasic(string $field = 'email', array $extraConditions = []): mixed
    {
        $credentials = $this->basicCredentials($this->getRequest(), $field);

        if ($this->once(array_merge($credentials, $extraConditions))) {
            return $this->getUser();
        }

        return false;
    }

    /**
     * @param $request
     * @return mixed
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
