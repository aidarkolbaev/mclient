<?php

namespace MClient;

class Memcached implements MemcachedInterface
{
    /** @var resource */
    private $connection;

    /** @var bool */
    private $asynchronous = false;

    /** @var string */
    private $noreply = "";

    /**
     * @param string $host
     * @param int $port
     * @throws \Exception
     */
    public function __construct($host = "127.0.0.1", $port = 11211)
    {
        $connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $ok = socket_set_option($connection, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 1, "usec" => 0]);
        $ok = socket_connect($connection, $host, $port) && $ok && $connection !== false;
        if (!$ok) {
            throw new \Exception(socket_last_error($connection));
        }
        $this->connection = $connection;
    }

    public function __destruct()
    {
        socket_close($this->connection);
    }

    /**
     * Memcached set command is used to set a new value to a new or existing key.
     * @param string $key
     * @param int|string $value
     * @param int $exptime
     * @param int $flags
     * @return bool
     */
    public function set($key, $value, $exptime = 0, $flags = 0)
    {
        // set key flags exptime bytes [noreply]
        // value
        if (!preg_match("/^\S+$/ui", $key) || empty(trim($value))) {
            return false;
        }
        $value = (string)$value;
        $bytes = strlen($value);
        $this->request("set {$key} {$flags} {$exptime} {$bytes}{$this->noreply}\r\n{$value}\r\n");
        return strpos($this->getResponse(), 'STORED') !== false;
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
        $length = 1024 + 30;
        if (is_array($key)) {
            $length = $length * count($key);
            $key = implode(" ", $key);
        }
        $this->request("get {$key}\r\n");
        return $this->parseResponse($this->getResponse($length));
    }

    /**
     * Memcached delete command is used to delete an existing key from the Memcached server.
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        // delete key [noreply]
        $this->request("delete {$key}{$this->noreply}\r\n");
        return preg_match("/(DELETED|NOT_FOUND)/", $this->getResponse()) == 1;
    }

    /**
     * @param $bool
     */
    public function async($bool)
    {
        $this->asynchronous = $bool;
        $this->noreply = $bool ? " noreply" : "";
        $bool ? socket_set_nonblock($this->connection) : socket_set_block($this->connection);
    }

    /**
     * Sends command to the Memcached server
     * @param $cmd
     * @return bool
     */
    private function request($cmd)
    {
        return (bool)socket_write($this->connection, $cmd);
    }


    /**
     * @param int $response_length
     * @return string
     */
    private function getResponse($response_length = 1024)
    {
        return socket_read($this->connection, $response_length);
    }

    /**
     * @param string $response
     * @return string|array
     */
    private function parseResponse($response)
    {
        $values = preg_split("/(\s*VALUE \S+ \d+ \d+[ \d]*|(\\r\\n)?(END|NOT_FOUND|ERROR))\s+/", $response);
        $values = array_filter($values);
        if (!empty($values)) {
            return count($values) === 1 ? array_shift($values) : $values;
        }
        return null;
    }
}
