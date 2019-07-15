<?php

namespace Tests;

use MClient\Memcached;
use MClient\MemcachedInterface;
use PHPUnit\Framework\TestCase;

class MemcachedTest extends TestCase
{
    public function testCreation()
    {
        $memcached = new Memcached();
        $this->assertInstanceOf(MemcachedInterface::class, $memcached);
        return $memcached;
    }

    /**
     * @depends testCreation
     */
    public function testSetValue(MemcachedInterface $memcached)
    {
        $this->assertTrue($memcached->set("testing", "Some value", 180));
        $this->assertTrue($memcached->set("test", "Some - \ntext\n", 180));
        $this->assertFalse($memcached->set("incorrect key", "Something", 180));
        return $memcached;
    }

    /**
     * @depends testSetValue
     */
    public function testGetValue(MemcachedInterface $memcached)
    {
        $this->assertEquals("Some value", $memcached->get("testing"));
        $this->assertEquals("Some - \ntext\n", $memcached->get("test"));
        $this->assertEmpty($memcached->get("nonexistent"));
        $this->assertEqualsCanonicalizing(["Some value", "Some - \ntext\n"], $memcached->get(["testing", "test"]));
        return $memcached;
    }

    /**
     * @depends testGetValue
     */
    public function testDeleteValue(MemcachedInterface $memcached)
    {
        $this->assertTrue($memcached->delete("test"));
        $this->assertTrue($memcached->delete("nonexistent"));
    }
}
