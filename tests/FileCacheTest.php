<?php

declare(strict_types=1);

namespace Tests;
use PHPUnit\Framework\TestCase;
use App\Services\Cache\FileCache;

class FileCacheTest extends TestCase
{
    private $cacheDirectory;
    private $cache;

    protected function setUp(): void
    {
        $this->cacheDirectory = sys_get_temp_dir() . '/test_cache';
        $this->createCacheDirectory();

        $this->cache = new FileCache($this->cacheDirectory);
    }

    protected function tearDown(): void
    {
        $this->deleteCacheDirectory();
    }

    public function testGetWithCacheHit()
    {
        $key = 'test_key';
        $value = 'test_value';
        $this->createCacheFile($key, $value);

        $result = $this->cache->get($key);

        $this->assertEquals($value, $result);
    }

    public function testGetWithCacheMiss()
    {
        $key = 'test_key';

        $result = $this->cache->get($key);

        $this->assertNull($result);
    }

    public function testSet()
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->cache->set($key, $value);

        $filename = $this->getCacheFilename($key);
        $this->assertFileExists($filename);
    }

    public function testDelete()
    {
        $key = 'test_key';
        $this->createCacheFile($key, 'test_value');

        $this->cache->delete($key);

        $filename = $this->getCacheFilename($key);
        $this->assertFileNotExists($filename);
    }

    private function createCacheDirectory()
    {
        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory);
        }
    }

    private function deleteCacheDirectory()
    {
        if (is_dir($this->cacheDirectory)) {
            array_map('unlink', glob($this->cacheDirectory . '/*'));
            rmdir($this->cacheDirectory);
        }
    }

    private function createCacheFile($key, $value)
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'expiration' => time() + 3600,
            'value' => $value,
        ];
        $contents = serialize($data);
        file_put_contents($filename, $contents);
    }

    private function getCacheFilename($key)
    {
        return $this->cacheDirectory . '/' . md5($key) . '.cache';
    }
}
