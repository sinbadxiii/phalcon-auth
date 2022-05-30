<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Providers\File;

use Sinbadxiii\PhalconAuth\Exceptions\Collection\JsonNotValidException;
use Sinbadxiii\PhalconAuth\Exceptions\Exception;
use Sinbadxiii\PhalconAuth\Users\UsersCollection;

/**
 * Class Parser
 * @package Sinbadxiii\PhalconAuth\Providers\Users\File
 */
class Parser
{
    /**
     * @param $config
     * @return UsersCollection
     * @throws Exception
     * @throws JsonNotValidException
     */
    public static function file($config)
    {
        if (!file_exists($config->path)) {
            throw new Exception("file dont exist");
        }

        $fileData = file_get_contents($config->path);

        return new UsersCollection(self::jsonValidate($fileData));
    }

    /**
     * @param $string
     * @return mixed
     * @throws JsonNotValidException
     */
    private static function jsonValidate($string)
    {
        $result = json_decode($string, true);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = '';
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if (!empty($error)) {
            throw new JsonNotValidException($error);
        }

        return $result;
    }
}
