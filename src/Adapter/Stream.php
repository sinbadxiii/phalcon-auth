<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use InvalidArgumentException;
use Exception;

/**
 * Class Stream
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Stream extends AbstractAdapter
{
    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getProviderStorage(): mixed
    {
        return $this->read($this->config->src);
    }

    /**
     * @param $path
     * @return mixed
     * @throws Exception
     */
    public function read(string $src): mixed
    {
        if (!file_exists($src)) {
            throw new Exception($src . " file don't exist");
        }

        $fileData = file_get_contents($src);

        return $this->validate($fileData, $src);
    }

    /**
     * @param $string
     * @param $filepath
     * @return mixed
     * @throws Exception
     */
    private function validate(mixed $fileData, string $src): mixed
    {
        $decoded = json_decode($fileData, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                $src . ' json_decode error: ' . json_last_error_msg()
            );
        }

        return $decoded;
    }
}