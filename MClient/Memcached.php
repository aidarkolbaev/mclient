<?php
namespace MClient;

class Memcached implements MemcachedInterface
{

    /**
     * Memcached set command is used to set a new value to a new or existing key.
     * @param string $key
     * @param int|string|float $value
     * @param int $expiration
     * @param int $flags
     * @return bool
     */
    public function set($key, $value, $expiration = 0, $flags = 0)
    {
        // TODO: Implement set() method.
    }

    /**
     * Memcached get command is used to get the value stored at key.
     * If the key does not exist in Memcached, then it returns null.
     * $key param must be string or an array that contains multiple keys
     * @param string|array $key
     * @return mixed
     */
    public function get($key)
    {
        // TODO: Implement get() method.
    }

    /**
     * Memcached delete command is used to delete an existing key from the Memcached server.
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        // TODO: Implement delete() method.
    }
}
