<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2020/2/19
 * Time: 15:58
 */

namespace App\WebSocket\Service;

use App\WebSocket\Cache\GameCache;
use App\WebSocket\Cache\RoomCache;

class PlayService
{
    use \EasySwoole\Component\CoroutineSingleTon;

    public function isTurn($roomId,$p)
    {
        return GameCache::getInstance()->isTurn($roomId,$p);
    }

    public function nextPlayer($roomId)
    {
        $p = GameCache::getInstance()->nextPlayer($roomId);
        return RoomCache::getInstance()->userBySeat($roomId,$p);
    }
}