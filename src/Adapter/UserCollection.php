<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Phalcon\Support\Collection;

/**
 * Class UserCollection
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class UserCollection extends Collection
{
    /**
     * @var array
     */
    protected static $collection;

    /**
     * UserCollection constructor.
     * @param array $data
     * @param bool $insensitive
     */
    public function __construct(array $data = [], bool $insensitive = true)
    {
        self::$collection = $data;
        parent::__construct($data, $insensitive);
    }

    /**
     * @param $term
     * @param string $field
     * @return array|null
     */
    public static function find(array $credentials)
    {
        $field = array_key_first($credentials) ?? "email";
        $term = $credentials[$field];

        $keys = array_keys(array_column(self::$collection, $field), $term);

        if ($keys !== null) {
            $result = array_map(static function($key) {
                return new User(self::$collection[$key] + ['id' => $key]);
            }, $keys);
        }

        return $result ?? null;
    }

    /**
     * @param array $credentials
     * @return \Sinbadxiii\PhalconAuth\Adapter\User|null
     */
    public static function first(array $credentials): ?User
    {
        $field = array_key_first($credentials) ?? "email";
        $term = $credentials[$field];

        $key = array_search($term, array_column(self::$collection, $field), true);

        if ($key !== false) {
            return new User(self::$collection[$key] + ['id' => $key]);
        }

        return null;
    }
}