<?php

namespace AddressBookBundle\Repository;

use Redis;
use Exception;

class RedisFactory
{
    public static function createRedis($host, $port, $db)
    {
        $redis = new Redis();
        if (!$redis->connect($host, $port)) {
            throw new Exception("Couldn't connect to redis at $host:$port");
        }
        $redis->select($db);
        return $redis;
    }
}
