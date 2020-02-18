<?php


namespace App\WebSocket\Cache;

use \EasySwoole\RedisPool\Redis;

class FdCache
{
    use \EasySwoole\Component\CoroutineSingleTon;

    private static $key = 'fd';

    public function saveFd($fd,$userId)
    {
        return Redis::invoke('redis', function ($redis) use ($fd,$userId) {
            $keyName = self::$key.":".$fd;
            $redis->set($keyName,$userId);
            $redis->expire($keyName,86400);
            return true;
        });
    }
}