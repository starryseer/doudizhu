<?php


namespace App\WebSocket\Cache;

use \EasySwoole\RedisPool\Redis;

class UserCache
{
    use \EasySwoole\Component\CoroutineSingleTon;

    private static $key = 'user';
    private static $value = ['id'=>0,'gold'=>0,'nickname'=>'','token'=>'','fd'=>0];

    public function saveUser($user,$fd)
    {
        return Redis::invoke('redis', function ($redis) use ($user,$fd) {
            $keyName = self::$key.":".$user['id'];
            $user['fd'] = $fd;
            $redis->hMset($keyName,$user);
            $redis->expire($keyName,86400);
            return true;
        });
    }

    public function getUser($id)
    {
        return Redis::invoke('redis', function ($redis) use ($id) {
            $keyName = self::$key.":".$id;
            return $redis->hgetall($keyName);
        });
    }
}