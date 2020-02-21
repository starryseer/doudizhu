<?php


namespace App\WebSocket\Common;

use \EasySwoole\EasySwoole\Config;

class Card
{
    public static function initCard()
    {
        $list = Config::getInstance()->getConf('card')['list'];
        $point = Config::getInstance()->getConf('card')['point'];
        shuffle($list);
        $cardInit = array_chunk($list,17);
        $c = [];
        foreach($cardInit as $key => $cards)
        {
            foreach ($cards as $card)
            {
                $c[$key][$point[$card]] = $card;
            }
            krsort($c[$key]);
            $c[$key] = array_values($c[$key]);
        }
        return $c;
    }

    public static function mergeCards($cards1,$cards2)
    {
        $pos = [];
        $index = 0;
        $point = Config::getInstance()->getConf('card')['point'];
        foreach ($cards1 as $key => $card)
        {
            do{
                if($point[$cards2[$index]] < $point[$card])
                    break;
                $pos[]=$key+$index;
                $index++;
            }while($index<count($cards2));

            if($index >= count($cards2))
                break;
        }

        foreach ($pos as $key => $p)
        {
            array_splice($cards1,$p,0,$cards2[$key]);
        }
        return $cards1;
    }

    public static function sortCard($cards)
    {
        $point = Config::getInstance()->getConf('card')['point'];
        $pos = [];
        foreach ($cards as $key => $card)
        {
            $pos[$point[$card]] = $card;
        }

        krsort($pos);
        $card = array_values($pos);
        return $card;
    }

}