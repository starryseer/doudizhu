<?php


namespace App\WebSocket\Cache;

use \EasySwoole\RedisPool\Redis;
use \App\WebSocket\Common\Card;

class GameCache
{
    use \EasySwoole\Component\CoroutineSingleTon;

    private static $key = 'game';
    private static $value = ['id'=>0,'c1'=>'','c2'=>'','c3'=>'','bottom'=>'','rob'=>'','lord'=>'','turn'=>'','lastCards'=>'','state'=>0,'num'=>0,'times'=>0];

    public function init($roomId)
    {
        $cards = Card::initCard();
        return Redis::invoke('redis', function ($redis)use($roomId,$cards) {
            $keyName = self::$key.":".$roomId;
            $rand = rand(0,2);
            $rand = 0;
            $rob['num'] = 0;
            $rob['rob'] = [];
            for($i=0;$i<=2;$i++)
            {
                $rob['turn'][] = ($rand+$i)%3+1;
            }
            $info = [
                'id' => $roomId,
//                'c1' => json_encode($cards[0],true),
//                'c2' => json_encode($cards[1],true),
//                'c3' => json_encode($cards[2],true),
//                'bottom' => json_encode($cards[3],true),
                'c1'=>json_encode([
                    'M0',
                    'M1',
                    'S2',
                    'H2',
                    'C2',
                    'D2',
                    'SA',
                    'HA',
                    'CA',
                    'DA',
                    'SK',
                    'HK',
                    'CK',
                    'DK',
                    'SQ',
                    'HQ',
                    'CQ',
                ],true),
                'c2'=>json_encode([
                    'DQ',
                    'SJ',
                    'HJ',
                    'CJ',
                    'DJ',
                    'S10',
                    'H10',
                    'C10',
                    'D10',
                    'S9',
                    'H9',
                    'C9',
                    'D9',
                    'S8',
                    'H8',
                    'C8',
                    'D8',
                ],true),
                'c3'=>json_encode([
                    'S7',
                    'H7',
                    'C7',
                    'D7',
                    'S6',
                    'H6',
                    'C6',
                    'D6',
                    'S5',
                    'H5',
                    'C5',
                    'D5',
                    'S4',
                    'H4',
                    'C4',
                    'D4',
                    'S3',
                ], true),
                'bottom'=>json_encode([
                    'H3',
                    'C3',
                    'D3',
                ],true),
                'rob'=>json_encode($rob,true),
                'lord'=>'',
                'turn'=>'',
                'state'=> 0,
                'lastCards'=>json_encode([[],[]],true)
            ];
            $redis->hMset($keyName,$info);
            $redis->expire($keyName,86400);
            return $info;
        });
    }

    public function getRob($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            $rob = $redis->hget($keyName,'rob');
            return json_decode($rob,true);
        });
    }

    public function setRob($roomId,$rob)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$rob) {
            $keyName = self::$key.":".$roomId;
            $rob = $redis->hset($keyName,'rob',json_encode($rob,true));
            return true;
        });
    }

    public function setLord($roomId,$lord)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$lord) {
            $keyName = self::$key.":".$roomId;
            list($bottom,$cards) = $redis->hMget($keyName,['bottom','c'.$lord]);
            $cards = Card::mergeCards(json_decode($cards,true),json_decode($bottom,true));
            $redis->hMset($keyName,[
                'c'.$lord=>json_encode($cards,true),
                'lord'=>$lord,
                'turn'=>$lord,
                'state'=>1
            ]);
            return true;
        });
    }

    public function getBottom($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            return json_decode($redis->hget($keyName,'bottom'),true);
        });
    }

    public function cardsBySeat($roomId,$p)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$p) {
            $keyName = self::$key.":".$roomId;
            return json_decode($redis->hget($keyName,'c'.$p),true);
        });
    }

    public function isTurn($roomId,$p)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$p) {
            $keyName = self::$key.":".$roomId;
            $seat = $redis->hget($keyName,'turn')%3;
            if($seat ==0)
                $seat = 3;
            if($seat == $p)
                return true;
            else
                return false;
        });
    }

    public function nextPlayer($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            $turn = $redis->hincrby($keyName,'turn',1)%3;
            if($turn == 0)
                $turn =3;
            return $turn;
        });
    }

    public function setCard($roomId,$p,$card)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$p,$card) {
            $keyName = self::$key.":".$roomId;
            $redis->hset($keyName,'c'.$p,json_encode($card,true));
            return true;
        });
    }

    public function lastCards($roomId)
    {
        return Redis::invoke('redis', function ($redis)use($roomId) {
            $keyName = self::$key.":".$roomId;
            return json_decode($redis->hget($keyName,'lastCards'),true);
        });
    }

    public function setLastCards($roomId,$lastCards)
    {
        return Redis::invoke('redis', function ($redis)use($roomId,$lastCards) {
            $keyName = self::$key.":".$roomId;
            return $redis->hset($keyName,'lastCards',json_encode($lastCards,true));
        });
    }
}