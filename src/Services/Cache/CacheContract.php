<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheContract
{
    /**
     * @param  string  $key
     * @return string|null
     */
    public function get(string $key): ?string;

    /**
     * @param  string  $key
     * @param  array  $value
     * @return void
     */
    public function set(string $key, array $value): void;

    /**
     * @param  string  $key
     * @return void
     */
    public function delete(string $key): void;
}