<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use InvalidArgumentException;
use Exception;

/**
 * Class Stream
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Stream extends Memory
{
    /**
     * @var string
     */
    protected string $srcFile;

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getData()
    {
        if ($this->config && !isset($this->config["src"])) {
            throw new InvalidArgumentException(
                "Сonfig key 'src' with path source file not exist"
            );
        }

        if (!empty($this->config["src"])) {
            $this->srcFile = $this->config["src"];
        }

        if (empty($this->data) && empty($this->srcFile)) {
            throw new InvalidArgumentException(
                "File source is empty"
            );
        }

        if (empty($this->data)) {
            $this->data = $this->read($this->srcFile);
        }

        if (empty($this->data)) {
            throw new InvalidArgumentException(
                "Data is empty"
            );
        }

        return $this->data;
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

    /**
     * @return string
     */
    public function getFileSource(): string
    {
        return $this->srcFile;
    }

    /**
     * @param string $src
     * @return void
     */
    public function setFileSource(string $src)
    {
        $this->srcFile = $src;
    }
}