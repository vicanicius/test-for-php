<?php

declare(strict_types=1);

namespace App\Services\Cache;

class FileCache implements CacheContract
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var int
     */
    private const TIME_CACHE_EXPIRATION = 3600;

    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        $filename = $this->getCacheFilename($key);

        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
            $data = unserialize($contents);
            if ($data['expiration'] >= time()) {
                return $data['value'];
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, $value): void
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'expiration' => time() + self::TIME_CACHE_EXPIRATION,
            'value' => $value,
        ];
        $contents = serialize($data);
        file_put_contents($filename, $contents);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * @param  string  $key
     * @return string
     */
    private function getCacheFilename(string $key): string
    {
        return $this->cacheDirectory . '/' . md5($key) . '.cache';
    }
}