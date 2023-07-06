<?php

declare(strict_types=1);

namespace App;

use App\Services\Cache\CacheContract;
use Exception;

use function file_get_contents;
use function http_build_query;
use function json_decode;
use function json_encode;
use function stream_context_create;

class HttpRequest
{
    private CacheContract $cache;

    public function __construct(CacheContract $cache )
    {
        $this->cache = $cache;
    }

    /**
     * @param  string  $url
     * @param  array|null  $parameters
     * @return array
     */
    public function get(string $url, array $parameters = null): array
    {
        try {
            $cacheKey = $this->generateCacheKey($url, $parameters);
            $cachedResponse = $this->cache->get($cacheKey);

            if ($cachedResponse) {
                return $cachedResponse;
            }

            $response = $this->call('GET', $url, $parameters);
            $this->cache->put($cacheKey, $response);

            return $response;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param  string  $url
     * @param  array|null  $parameters
     * @param  array|null  $data
     * @return array
     */
    public function post(string $url, array $parameters = null, array $data = null): array
    {
        try {
            return $this->call('POST', $url, $parameters, $data);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param  string  $url
     * @param  array|null  $parameters
     * @param  array|null  $data
     * @return array
     */
    public function put(string $url, array $parameters = null, array $data = null): array
    {
        try {
            $this->clearCacheIfExists($url, $parameters);
            return $this->call('PUT', $url, $parameters, $data);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param  string  $url
     * @param  array|null  $parameters
     * @return array
     */
    public function delete(string $url, array $parameters = null): array
    {
        try {
            $this->clearCacheIfExists($url, $parameters);
            return $this->call('DELETE', $url, $parameters);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param  string  $method
     * @param  string  $url
     * @param  array|null  $parameters
     * @param  array|null  $data
     * @return array
     */
    private function call(string $method, string $url, array $parameters = null, array $data = null): array
    {
        $opts = [
            'http' => [
                'method'  => $method,
                'header'  => 'Content-type: application/json',
                'content' => $data ? json_encode($data) : null
            ]
        ];

        $url .= ($parameters ? '?' . http_build_query($parameters) : '');
        
        $response = file_get_contents($url, false, stream_context_create($opts));
        
        return json_decode($response, true);
    }

    /**
     * @param  string  $url
     * @param  array|null  $parameters
     * @return string
     */
    private function generateCacheKey(string $url, ?array $parameters): string
    {
        $key = $url . json_encode($parameters);
        return md5($key);
    }

    /**
     * @param  string  $url
     * @param  array  $parameters
     * @return void
     */
    private function clearCacheIfExists(string $url, array $parameters): void
    {
        $cacheKey = $this->generateCacheKey($url, $parameters);
        $cachedResponse = $this->cache->get($cacheKey);

        if ($cachedResponse) {
            $this->cache->delete($cacheKey);
        }
    }
}
