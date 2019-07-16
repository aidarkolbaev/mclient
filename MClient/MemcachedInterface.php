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
     * $key param must be string or an array that contains multiple keys.
     *
     * If asynchronous mode is enabled, it returns true on successful request,
     * and you can retrieve the values by using the receive() method
     * @param string|array|bool $key
     * @return mixed
     */
    public function get($key);

    /**
     * Memcached delete command is used to delete an existing key from the Memcached server.
     * Returns true if $key is deleted or not found
     * @param string $key
     * @return bool
     */
    public function delete($key);

    /**
     * Perform requests asynchronously
     * @param $bool
     * @return void
     */
    public function async($bool);

    /**
     * Retrieves values called by get() method in asynchronous mode
     * @return string|array
     */
    public function receive();
}
