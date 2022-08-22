<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

/**
 * Class Stream
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Stream extends CollectionAdapterAbstract implements AdapterInterface
{
    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getData()
    {
        return $this->read($this->config->src);
    }

    /**
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    public function read($path): mixed
    {
        if (!file_exists($path)) {
            throw new \Exception("file dont exist");
        }

        $fileData = file_get_contents($path);

        return $this->validate($fileData, $path);
    }

    /**
     * @param $string
     * @param $filepath
     * @return mixed
     * @throws \Exception
     */
    private function validate($string, $filepath): mixed
    {
        $decoded = json_decode($string, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                $filepath . ' json_decode error: ' . json_last_error_msg()
            );
        }

        return $decoded;
    }
}