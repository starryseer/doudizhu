<?php


namespace App\WebSocket\Service;

use App\WebSocket\Cache\RoomCache;
use App\WebSocket\Cache\PlayerCache;

class RoomService
{
    use \EasySwoole\Component\CoroutineSingleTon;


    public function createRoom($user,$conf)
    {
        $room = RoomCache::getInstance()->createRoom($user,$conf);
        if(!empty($room))
            PlayerCache::getInstance()->createPlayer($room['id'],$user['id'],$user['fd']);
        return $room;
    }

    public function joinRoom($user,$roomId)
    {

        if($this->canJoinRoom($user['id'],$roomId))
            return $this->_joinRoom($user,$roomId);

        return [];
    }

    public function canJoinRoom($userId,$roomId)
    {
        return RoomCache::getInstance()->canJoinRoom($roomId) and !PlayerCache::getInstance()->hasPlayer($userId,$roomId);
    }

    public function _joinRoom($user,$roomId)
    {
        if(!PlayerCache::getInstance()->createPlayer($roomId,$user['id'],$user['fd']))
            return [];

        if(!RoomCache::getInstance()->addNumRoom($roomId))
        {
            PlayerCache::getInstance()->deletePlayer($user['id'],$roomId);
            return [];
        }

        $room = RoomCache::getInstance()->addUser($user,$roomId);
        if(empty($room))
            PlayerCache::getInstance()->deletePlayer($user['id'],$roomId);

        return $room;
    }

    public function readyRoom($userId,$roomId)
    {
        $user = RoomCache::getInstance()->getUser($userId,$roomId);
        if(empty($user) or $user['ready'] == 1)
            return [];

        if(empty($user = RoomCache::getInstance()->readyUser($user,$roomId)))
            return [];

        return $user;
    }

    public function getUser($userId,$roomId)
    {
        return RoomCache::getInstance()->getUser($userId,$roomId);
    }

    public function getOtherFds($userId,$roomId,$other = true)
    {
        $users = RoomCache::getInstance()->getUsers($roomId);
        $fds = [];
        foreach($users as $user)
        {
            if($other and $user['id'] == $userId)
                continue;
            $fds[$user['p']]=$user['fd'];
        }
        return $fds;
    }

    public function allReady($roomId)
    {
        $sum = 0;
        $userList =RoomCache::getInstance()->getUsers($roomId);
        if(count($userList) == 3)
        {
            foreach($userList as $user)
            {
                $sum+=$user['ready'];
            }
        }
        if($sum == 3 and RoomCache::getInstance()->setReady($roomId))
            return true;

        return false;
    }

    public function restart($roomId)
    {
        RoomCache::getInstance()->restart($roomId);
    }
}