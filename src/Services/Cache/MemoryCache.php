<?php

declare(strict_types=1);

namespace App\Services\Cache;

class MemoryCache implements CacheContract
{
    /**
     * @var int
     */
    private const TIME_CACHE_EXPIRATION = 3600;
    
    /**
     * @var array
     */
    private $cache = [];

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        if (isset($this->cache[$key])) {
            $data = $this->cache[$key];
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
        $data = [
            'expiration' => time() + self::TIME_CACHE_EXPIRATION,
            'value' => $value,
        ];
        $this->cache[$key] = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        unset($this->cache[$key]);
    }
}