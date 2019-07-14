<?php

namespace MClient\Tests;

use MClient\Memcached;
use MClient\MemcachedInterface;
use PHPUnit\Framework\TestCase;

class MemcachedTest extends TestCase
{
    public function testCreation() {
        $memcached = new Memcached();
        $this->assertInstanceOf(MemcachedInterface::class, $memcached);
        return $memcached;
    }

    /**
     * @depends testCreation
     */
    public function testSetValue(MemcachedInterface $memcached)
    {
        $this->assertTrue($memcached->set("testing", "Some value", 180), "Set Value error");
        $this->assertTrue($memcached->set("test", "Some - text", 180), "Set Value error");
        $this->assertFalse($memcached->set("incorrect key", "Something", 180), "Set Value error");
        return $memcached;
    }

    /**
     * @depends testSetValue
     */
    public function testGetValue(MemcachedInterface $memcached)
    {
        $this->assertEquals("Some value", $memcached->get("testing"), "Not Equal values");
        $this->assertEquals("Some - text", $memcached->get("test"), "Not Equal values");
        $this->assertEmpty($memcached->get("nonexistent"), "Not null");
        $this->assertEqualsCanonicalizing(["Some value", "Some - text"], $memcached->get(["testing", "test"]), "Not Equal arrays");
    }
}
