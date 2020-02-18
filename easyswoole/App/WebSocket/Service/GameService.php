<?php


namespace App\WebSocket\Service;

use App\WebSocket\Cache\GameCache;

class GameService
{
    use \EasySwoole\Component\CoroutineSingleTon;

    public function init($roomId)
    {
        return GameCache::getInstance()->init($roomId);
    }

    public function getBottom($roomId)
    {
        return GameCache::getInstance()->getBottom($roomId);
    }

    public function cardsBySeat($roomId,$p)
    {
        return GameCache::getInstance()->cardsBySeat($roomId,$p);
    }
}