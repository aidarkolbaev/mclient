<?php

namespace MClient;

/**
 * Pure PHP Memcached client
 */
interface MemcachedInterface
{
    /**
     * Memcached set command is used to set a new value to a new or existing key.
     * @param string $key
     * @param int|string $value
     * @param int $exptime
     * @param int $flags
     * @return bool
     */
    public function set($key, $value, $exptime = 0, $flags = 0);

    /**
     * Memcached get command is used to get the value stored at key.
     * If the key does not exist in Memcached, then it returns null.
     * $key param must be string or an array that contains multiple keys
     * @param string|array $key
     * @return mixed
     */
    public function get($key);

    /**
     * Memcached delete command is used to delete an existing key from the Memcached server.
     * @param string $key
     * @return bool
     */
    public function delete($key);
}
