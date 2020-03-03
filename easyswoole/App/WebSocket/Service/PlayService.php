<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2020/2/19
 * Time: 15:58
 */

namespace App\WebSocket\Service;

use App\WebSocket\Service\GameService;
use App\WebSocket\Cache\GameCache;
use App\WebSocket\Cache\RoomCache;
use App\WebSocket\Common\Card;

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

    public function hasCard($roomId,$p,$card)
    {
        $cards = GameCache::getInstance()->cardsBySeat($roomId,$p);
        $cardInter = array_intersect($cards,$card);
        if(count($cardInter) == count($card))
            return true;
        else
            return false;
    }

    public function removeCard($roomId,$p,$card)
    {
        $cards = GameCache::getInstance()->cardsBySeat($roomId,$p);
        $cardDiff = array_diff($cards,$card);
        $cards = array_values($cardDiff);
        if(!GameCache::getInstance()->setCard($roomId,$p,$cards))
            return [];
        return Card::sortCard($card);
    }

    public function lastCard($roomId)
    {
        $lastCards = GameCache::getInstance()->lastCards($roomId);
        $lastCard = [];
        foreach($lastCards as $lastC)
        {
            if(!empty($lastC))
            {
                $lastCard = $lastC;
                break;
            }
        }
        return $lastCard;
    }

    public function setLastCard($roomId,$cards)
    {
        $lastCards = GameCache::getInstance()->lastCards($roomId);
        array_pop($lastCards);
        array_unshift($lastCards,$cards);
        return GameCache::getInstance()->setLastCards($roomId,$lastCards);
    }

    public function tipCard($roomId,$p)
    {
        $lastCard = $this->lastCard($roomId);
        $card = GameService::getInstance()->cardsBySeat($roomId,$p);
        return Card::tipCard($card,$lastCard);
    }

    public function isEnd($roomId,$p)
    {
        $card = GameService::getInstance()->cardsBySeat($roomId,$p);
        if(empty($card))
            return true;

        return false;
    }
}