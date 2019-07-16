#### MClient - pure php memcached client 

[![Build Status](https://travis-ci.org/aidarkolbaev/memclient.svg?branch=master)](https://travis-ci.org/aidarkolbaev/memclient)


#### Getting started

```bash
$ composer require aidarkolbaev/memclient
```

```php
$memcached = new \MClient\Memcached();

// In seconds
$expiration = 30;

// to store value
$memcached->set("key", "value", $expiration);

// to retrieve value
$memcached->get("key");

// to delete value
$memcached->delete("key");

// to enable asynchronous mode
$memcached->async(true);

// Retrieves values called by get() method in asynchronous mode
$memcached->receive();
```
