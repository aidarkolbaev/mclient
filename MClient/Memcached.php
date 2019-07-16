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

    /** @var int */
    private $asyncRequestsCount = 0;

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
        if (!preg_match("/^\S+$/ui", $key) || empty(trim($value))) {
            return false;
        }
        $value = (string)$value;
        $bytes = strlen($value);
        $ok = $this->request("set {$key} {$flags} {$exptime} {$bytes}{$this->noreply}\r\n{$value}\r\n");
        return $this->asynchronous ? $ok : strpos($this->getResponse(), 'STORED') !== false;
    }

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
    public function get($key)
    {
        $length = 1024 + 30;
        $keys = [];
        if (is_array($key)) {
            $keys = $key;
            $length = $length * count($key);
            $key = implode(" ", $key);
        }
        $ok = $this->request("get {$key}\r\n");
        if ($this->asynchronous) {
            $this->asyncRequestsCount++;
            return $ok;
        }
        $response = $this->parseResponse($this->getResponse($length));
        if (is_array($response)) {
            if (!empty($keys) && is_array($keys)) {
                $result = array_intersect_key(($response),array_flip($keys));
                return !empty($result) ? $result : null;
            }
            return !empty($response[$key]) ? $response[$key] : null;
        }
        return $response;
    }

    /**
     * Memcached delete command is used to delete an existing key from the Memcached server.
     * Returns true if $key is deleted or not found
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $ok = $this->request("delete {$key}{$this->noreply}\r\n");
        return $this->asynchronous ? $ok : preg_match("/(DELETED|NOT_FOUND)/", $this->getResponse()) == 1;
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
     * Retrieves values called by get() method in asynchronous mode
     * @return string|array
     */
    public function receive()
    {
        $length = (1024 + 30) * $this->asyncRequestsCount;
        $timeout = $this->asyncRequestsCount * 50;
        $this->asyncRequestsCount = 0;
        return $this->parseResponse($this->getResponse($length, $timeout));
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
     * @param int $timeoutInMs
     * @return string
     */
    private function getResponse($response_length = 1024, $timeoutInMs = 10)
    {
        $response = "";
        while (true) {
            $read = [$this->connection];
            $write = null;
            $except = null;
            $numOfChanges = socket_select($read, $write, $except, 0);
            if ($numOfChanges === 0) {
                if ($timeoutInMs <= 0) {
                    break;
                }
                usleep(10 * 1000);
                $timeoutInMs -= 10;
                continue;
            } elseif ($numOfChanges > 0) {
                $value = socket_read($this->connection, $response_length);
                $response .= $value;
                if (preg_match("/^.*(STORED|END|NOT_FOUND|(CLIENT_|SERVER_)?ERROR|DELETED)\r\n$/m", $value)) {
                    break;
                }
            }
        }
        return $response;
    }

    /**
     * @param string $response
     * @return string|array
     */
    private function parseResponse($response)
    {
        $matches = [];
        preg_match_all("/\s*VALUE (?<keys>\S+) \d+ \d+[ \d]*\s+/", $response, $matches);
        $values = preg_split("/(\s*VALUE \S+ \d+ \d+[ \d]*|(\\r\\n)?(END|NOT_FOUND|ERROR))\s+/", $response);
        $values = array_filter($values);
        $values = array_values($values);
        if (!empty($values)) {
            if (count($values) === 1) {
                return array_shift($values);
            }
            $result = [];
            for ($i = 0; $i < count($values); $i++) {
                $result[$matches["keys"][$i]] = $values[$i];
            }
            return $result;
        }
        return null;
    }
}
