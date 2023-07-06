<?php

declare(strict_types=1);

namespace Tests;
use PHPUnit\Framework\TestCase;
use App\Services\Cache\MemoryCache;

class MemoryCacheTest extends TestCase
{
    private $cache;

    protected function setUp(): void
    {
        $this->cache = new MemoryCache();
    }

    public function testGetWithCacheHit()
    {
        $key = 'test_key';
        $value = 'test_value';
        $this->cache->set($key, $value);

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

        $this->assertArrayHasKey($key, $this->getCacheData());
    }

    public function testDelete()
    {
        $key = 'test_key';
        $this->cache->set($key, 'test_value');

        $this->cache->delete($key);

        $this->assertArrayNotHasKey($key, $this->getCacheData());
    }

    private function getCacheData()
    {
        $reflection = new ReflectionClass($this->cache);
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);

        return $property->getValue($this->cache);
    }
}