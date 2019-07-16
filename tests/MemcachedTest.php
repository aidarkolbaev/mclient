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
        $this->assertTrue($memcached->delete("testing"));
        return $memcached;
    }

    /**
     * @depends testDeleteValue
     */
    public function testAsyncSet(MemcachedInterface $memcached)
    {
        $memcached->async(true);
        $this->assertTrue($memcached->set("async_test", "Memcached", 10));
        $this->assertTrue($memcached->set("key", "Value", 10));
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam molestie nisi a dolor
             fringilla. Nunc massa nisi, dignissim ac tempor sed, rutrum a neque. Mauris et diam congue,
             urna vitae, sagittis ante. Sed id fringilla justo. Aliquam vitae varius ante.
             nt in sem nibh. Curabitur vitae erat sed urna lacinia molestie. In viverra mollis diam at blandit.
             arius eu arcu vel commodo. Vestibulum eget metus eu risus faucibus ultrices.
             am aliquam lectus risus, eu aliquam ex fermentum in. 
             am pellentesque non tellus quis fringilla. Etiam condimentum est purus,
             rutrum mauris lacinia vitae. Aenean aliquam nulla tellus, nec volutpat dui placerat eu.
             am ex enim, eleifend ultricies laoreet sit amet, vulputate et ex.
             nec sit amet finibus odio, rutrum congue lorem. Nunc facilisis gravida velit,
             el ullamcorper ex tempus vitae. Proin a risus vitae libero feugiat rhoncus placerat
             eu justo. Duis porta nec mi eu commodo. Suspendisse diam risus, pellentesque ut consequat id,
             mentum vel diam. Cras iaculis mi nec porta semper.";
        $this->assertTrue($memcached->set("big_value", $text, 10));
        return $memcached;
    }

    /**
     * @depends testAsyncSet
     */
    public function testAsyncGet(MemcachedInterface $memcached)
    {
        $this->assertTrue($memcached->get("async_test"));
        $this->assertTrue($memcached->get("key"));
        $this->assertTrue($memcached->get("big_value"));
        $expected = [
            "Memcached",
            "Value",
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam molestie nisi a dolor
             fringilla. Nunc massa nisi, dignissim ac tempor sed, rutrum a neque. Mauris et diam congue,
             urna vitae, sagittis ante. Sed id fringilla justo. Aliquam vitae varius ante.
             nt in sem nibh. Curabitur vitae erat sed urna lacinia molestie. In viverra mollis diam at blandit.
             arius eu arcu vel commodo. Vestibulum eget metus eu risus faucibus ultrices.
             am aliquam lectus risus, eu aliquam ex fermentum in. 
             am pellentesque non tellus quis fringilla. Etiam condimentum est purus,
             rutrum mauris lacinia vitae. Aenean aliquam nulla tellus, nec volutpat dui placerat eu.
             am ex enim, eleifend ultricies laoreet sit amet, vulputate et ex.
             nec sit amet finibus odio, rutrum congue lorem. Nunc facilisis gravida velit,
             el ullamcorper ex tempus vitae. Proin a risus vitae libero feugiat rhoncus placerat
             eu justo. Duis porta nec mi eu commodo. Suspendisse diam risus, pellentesque ut consequat id,
             mentum vel diam. Cras iaculis mi nec porta semper."
        ];
        $this->assertEqualsCanonicalizing($expected, $memcached->receive());
        return $memcached;
    }

    /**
     * @depends testAsyncGet
     */
    public function testAsyncDelete(MemcachedInterface $memcached)
    {
        $this->assertTrue($memcached->delete("key"));
        $this->assertTrue($memcached->delete("async_test"));
    }
}
