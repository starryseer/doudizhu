<?php


namespace App\WebSocket\Cache;

use \EasySwoole\RedisPool\Redis;

class RoomCache
{
    use \EasySwoole\Component\CoroutineSingleTon;

    private static $key = 'room';
    private static $value = ['id'=>0,'p1'=>'','p2'=>'','p3'=>'','state'=>0,'num'=>0,'times'=>0];

    private function autoIncreaseId()
    {
        return Redis::invoke('redis', function ($redis) {
            $id = $redis->incrby(self::$key,1)%1000000;
            if($id == 0)
                $redis->set(self::$key,1000);
            $length = strlen((string)$id);
            return str_repeat('0',(6-$length)).$id;
        });
    }

    public function createRoom($user,$conf)
    {
        $roomId = $this->autoIncreaseId();
        return Redis::invoke('redis', function ($redis)use($user,$roomId,$conf) {
            $keyName = self::$key.":".$roomId;
            $user['p'] = 1;
            $user['ready'] = 0;
            $info = [
                'id' => $roomId,
                'p1' => json_encode($user,true),
                'state'=> 0,
                'num' => 1,
                'times' => $conf['times'],
                'bottom' => $conf['bottom'],
            ];
            $redis->hMset($keyName,$info);
            $redis->expire($keyName,86400);
            return $info;
        });
    }

    public function canJoinRoom($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            $num = $redis->hget($keyName,'num');
            if($num and $num > 0 and $num <3)
                return true;
            return false;
        });
    }

    public function addNumRoom($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            $num = $redis->hincrby($keyName,'num',1);
            if($num >3)
            {
                $redis->hincrby($keyName,'num',-1);
                return false;
            }

            return true;
        });
    }

    public function addUser($user,$roomId)
    {
        return Redis::invoke('redis', function ($redis)use($user,$roomId) {
            $keyName = self::$key.":".$roomId;
            for($i=0;$i<=5;$i++)
            {
                $key = 'p'.($i%3+1);
                $user['p'] = $i%3+1;
                $user['ready'] = 0;
                if($redis->hsetnx($keyName,$key,json_encode($user,true)))
                {
                    return $redis->hgetall($keyName);
                }
            }

            $redis->hincrby($keyName,'num',-1);
            return [];
        });
    }

    public function getUsers($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            $keys = ['p1','p2','p3'];
            $users = $redis->hmget($keyName,$keys);
            $usersInfo = [];
            foreach($users as $user)
            {
                if(!empty($user))
                {
                    array_push($usersInfo ,json_decode($user,true));
                }
            }
            return $usersInfo;
        });
    }

    public function getUser($userId,$roomId)
    {
        return Redis::invoke('redis', function ($redis)use($userId,$roomId) {
            $keyName = self::$key.":".$roomId;
            $keys = ['p1','p2','p3'];
            $users = $redis->hmget($keyName,$keys);
            foreach($users as $user)
            {
                $user = json_decode($user,true);
                if(!empty($user) and $user['id'] == $userId)
                    return $user;
            }
            return [];
        });
    }

    public function readyUser($user,$roomId)
    {
        return Redis::invoke('redis', function ($redis)use($user,$roomId) {
            $keyName = self::$key.":".$roomId;
            $user['ready'] = 1;
            $redis->hset($keyName,'p'.$user['p'],json_encode($user,true));
            return $user;
        });
    }

    public function setReady($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            $result = $redis->hincrby($keyName,'state',1);
            if($result == 1)
                return true;
            $redis->hincrby($keyName,'state',-1);
            return false;
        });
    }

    public function userBySeat($roomId,$p)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$p) {
            $keyName = self::$key.":".$roomId;
            return json_decode($redis->hget($keyName,'p'.$p),true);
        });
    }
}