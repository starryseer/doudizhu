<?php


namespace App\WebSocket\Service;

use App\WebSocket\Cache\GameCache;
use App\WebSocket\Cache\RoomCache;

class RobService
{
    use \EasySwoole\Component\CoroutineSingleTon;

    public function robUser($roomId)
    {
        $rob = GameCache::getInstance()->getRob($roomId);
        return RoomCache::getInstance()->userBySeat($roomId,$rob['turn'][$rob['num']]);
    }

    public function getRob($roomId)
    {
        return GameCache::getInstance()->getRob($roomId);
    }

    public function addRob($roomId,$rob,$isRob)
    {
        if($isRob)
            $rob['rob'][] = $rob['turn'][$rob['num']];
        $rob['num']++;
        if(GameCache::getInstance()->setRob($roomId,$rob))
            return $rob;

        return [];

    }

    public function isEnd($rob)
    {
        if(($rob['num'] == 2 && count($rob['rob']) == 0) || $rob['num'] == 3)
            return true;

        return false;
    }

    public function setLord($roomId,$rob)
    {
        if($rob['num'] == 2)
        {
            GameCache::getInstance()->setLord($roomId,$rob['turn'][2]);
            return $rob['turn'][2];
        }
        else if($rob['num'] == 3)
        {
            GameCache::getInstance()->setLord($roomId,$rob['rob'][count($rob['rob'])-1]);
            return $rob['rob'][count($rob['rob'])-1];
        }

    }
}