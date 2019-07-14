<?php

namespace MClient;

class Memcached implements MemcachedInterface
{
    /** @var resource */
    private $connection;

    /**
     * @param string $host
     * @param int $port
     * @throws \Exception
     */
    public function __construct($host = "127.0.0.1", $port = 11211)
    {
        $connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($connection, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 1, "usec" => 0]);
        if (!socket_connect($connection, $host, $port)) {
            socket_close($connection);
            throw new \Exception("Connection refused");
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
        if (!preg_match("/^\S+$/ui", $key)) {
            return false;
        }
        $value = (string)$value;
        $bytes = strlen($value);
        $command = "set {$key} {$flags} {$exptime} {$bytes}\r\n{$value}\r\n";
        return strpos($this->request($command), 'STORED') !== false;
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
        $command = "get {$key}\r\n";
        $response = $this->parseResponse($this->request($command, $length));
        return !empty($response) ? $response : null;
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


    /**
     * Executes command and returns response
     * @param $cmd
     * @param int $response_length
     * @return string
     */
    private function request($cmd, $response_length = 1024)
    {
        socket_write($this->connection, $cmd);
        return socket_read($this->connection, $response_length);
    }

    /**
     * @param string $response
     * @return string|array
     */
    private function parseResponse($response) {
        $matches = [];
        preg_match_all("/VALUE \S+ \d+ (?<bytes>\d+)[ \d]*\s+/", $response, $matches);
        $values = [];
        for ($i = 0; $i < count($matches[0]); $i++) {
            $response = preg_replace("/\s*" . $matches[0][$i] . "\s*/", "", $response);
            $values[] = substr($response,0, (int)$matches['bytes'][$i]);
            $response = str_replace($values[$i], "", $response);
        }
        return count($values) === 1 ? array_shift($values) : $values;
    }
}
