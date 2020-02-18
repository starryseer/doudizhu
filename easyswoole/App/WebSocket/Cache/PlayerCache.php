<?php


namespace App\WebSocket\Cache;

use \EasySwoole\RedisPool\Redis;

class PlayerCache
{
    use \EasySwoole\Component\CoroutineSingleTon;

    private static $key = 'player';
    private static $value = ['id'=>'fd'];

    public function hasPlayer($userId,$roomId)
    {
        return Redis::invoke('redis', function ($redis) use ($userId,$roomId) {
            $keyName = self::$key.":".$roomId;
            return $redis->hexists($keyName,$userId);
        });
    }

    public function allPlayers($roomId)
    {
        return Redis::invoke('redis', function ($redis) use ($roomId) {
            $keyName = self::$key.":".$roomId;
            return $redis->hgetall($keyName);
        });
    }

    public function createPlayer($roomId,$userId,$fd)
    {
        return Redis::invoke('redis', function ($redis) use ($roomId,$userId,$fd) {
            $keyName = self::$key.":".$roomId;
            $result = $redis->hsetnx($keyName,$userId,$fd);
            if($result)
                $redis->expire($keyName,86400);
            return $result;
        });
    }

    public function deletePlayer($roomId,$userId)
    {
        return Redis::invoke('redis', function ($redis) use ($roomId,$userId) {
            $keyName = self::$key.":".$roomId;
            return $redis->hdel($keyName,$userId);
        });
    }
}