<?php


namespace App\WebSocket\Cache;

use \EasySwoole\RedisPool\Redis;
use \App\WebSocket\Common\Card;
class GameCache
{
    use \EasySwoole\Component\CoroutineSingleTon;

    private static $key = 'game';
    private static $value = ['id'=>0,'c1'=>'','c2'=>'','c3'=>'','state'=>0,'num'=>0,'times'=>0];

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
                'c1' => json_encode($cards[0],true),
                'c2' => json_encode($cards[1],true),
                'c3' => json_encode($cards[2],true),
                'bottom' => json_encode($cards[3],true),
                'rob'=>json_encode($rob,true),
                'lord'=>'',
                'turn'=>'',
                'state'=> 0,
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
}