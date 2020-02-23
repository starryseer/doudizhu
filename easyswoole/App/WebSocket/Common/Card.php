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

    public static function cardType($cards)
    {
        $type = [];
        switch (count($cards))
        {
            case 1:
                if(static::isSingle($cards))
                    $type = static::single($cards);
                break;
            case 2:
                if(static::isPair($cards))
                    $type = static::pair($cards);
                elseif(static::isBomb($cards))
                    $type = static::bomb($cards);
                break;
            case 3:
                if(static::isTriple($cards))
                    $type = static::triple($cards);
                break;
            case 4:
                if(static::isBomb($cards))
                    $type = static::bomb($cards);
                elseif(static::isThreeOne($cards))
                    $type = static::threeOne($cards);
                break;
            case 5:
                if(static::isThreePair($cards))
                    $type = static::threePair($cards);
                elseif(static::isStraight($cards))
                    $type = static::straight($cards);
                break;
            case 6:
                if(static::isFourTwo($cards))
                    $type = static::fourTwo($cards);
                elseif(static::isFlight($cards))
                    $type = static::flight($cards);
                elseif(static::isStraight($cards))
                    $type = static::straight($cards);
                elseif(static::isCompany($cards))
                    $type = static::company($cards);
                break;
            case 8:
                if(static::isFourPair($cards))
                    $type = static::fourPair($cards);
                elseif(static::isFlightTwo($cards))
                    $type = static::flightTwo($cards);
                elseif(static::isStraight($cards))
                    $type = static::straight($cards);
                elseif(static::isCompany($cards))
                    $type = static::company($cards);
                break;
            case 10:
                if(static::isFlightPair($cards))
                    $type = static::flightPair($cards);
                elseif(static::isStraight($cards))
                    $type = static::straight($cards);
                elseif(static::isCompany($cards))
                    $type = static::company($cards);
                break;
            default:
                if(static::isStraight($cards))
                    $type = static::straight($cards);
                elseif(static::isCompany($cards))
                    $type = static::company($cards);
                break;
        }
        return $type;
    }

    public static function isSingle($cards)
    {
        if(count($cards) == 1)
            return true;
        return false;
    }

    public static function isPair($cards)
    {
        if(count($cards) == 2 and substr($cards[0],1,2) == substr($cards[1],1,2))
            return true;
        return false;
    }

    public static function isTriple($cards)
    {
        if(count($cards) == 3 and substr($cards[0],1,2) == substr($cards[1],1,2) and substr($cards[1],1,2) == substr($cards[2],1,2))
            return true;
        return false;
    }

    public static function isBomb($cards)
    {
        if(count($cards) == 2 and sort($cards) and $cards == ['M0','M1'])
            return true;
        if(count($cards) == 4 and substr($cards[0],1,2) == substr($cards[1],1,2) and substr($cards[1],1,2) == substr($cards[2],1,2) and substr($cards[2],1,2) == substr($cards[3],1,2))
            return true;
        return false;
    }

    public static function isThreeOne($cards)
    {
        if(count($cards) != 4)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_values(array_count_values($cardList));
        sort($times);
        if($times == [1,3])
            return true;

        return false;
    }

    public static function isThreePair($cards)
    {
        if(count($cards) != 5)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_values(array_count_values($cardList));
        sort($times);
        if($times == [2,3])
            return true;

        return false;
    }

    public static function isFourTwo($cards)
    {
        if(count($cards) != 6)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_values(array_count_values($cardList));
        if(max($times) == 4)
            return true;

        return false;
    }

    public static function isFourPair($cards)
    {
        if(count($cards) != 8)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_values(array_count_values($cardList));
        sort($times);
        if($times == [2,2,4])
            return true;

        return false;
    }

    public static function isFlight($cards)
    {
        if(count($cards) != 6)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_count_values($cardList);
        if(array_values($times) == [3,3])
        {
            $keys = array_keys($times);
            if(abs($keys[0] - $keys[1])==1)
                return true;
        }
        return false;
    }

    public static function isFlightTwo($cards)
    {
        if(count($cards) != 8)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_count_values($cardList);
        $arrTimes = array_values($times);
        sort($arrTimes);
        if($arrTimes == [2,3,3] or $arrTimes == [1,1,3,3])
        {
            $keys = [];
            foreach($times as $key =>$value)
            {
                if($value == 3)
                    $keys[] = $key;
            }
            $cardValue = Config::getInstance()->getConf('card')['value'];
            if(abs($cardValue[$keys[0]] - $cardValue[$keys[1]])==1)
                return true;
        }

        return false;
    }

    public static function isFlightPair($cards)
    {
        if(count($cards) != 10)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_count_values($cardList);
        $arrTimes = array_values($times);
        sort($arrTimes);
        if($arrTimes == [2,2,3,3])
        {
            $keys = [];
            foreach($times as $key =>$value)
            {
                if($value == 3)
                    $keys[] = $key;
            }
            $cardValue = Config::getInstance()->getConf('card')['value'];
            if(abs($cardValue[$keys[0]] - $cardValue[$keys[1]])==1)
                return true;
        }
        return false;
    }

    public static function isStraight($cards)
    {
        if(count($cards) <5)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_count_values($cardList);
        if(max($times)!=1)
            return false;

        $cardValue = Config::getInstance()->getConf('card')['value'];
        $keys = array_keys($times);
        $point = [];
        foreach($keys as $key)
        {
            $point[] = $cardValue[$key];
        }
        sort($point);
        if($point[count($point)-1] <=12 and ($point[count($point)-1] - $point[0]+1)==count($point))
            return true;

        return false;
    }

    public static function isCompany($cards)
    {
        if(count($cards) <6)
            return false;

        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= substr($card,1,2);
        }
        $times = array_count_values($cardList);
        if(max($times) !=2 or min($times) != 2)
            return false;

        $cardValue = Config::getInstance()->getConf('card')['value'];
        $keys = array_keys($times);
        $point = [];
        foreach($keys as $key)
        {
            $point[] = $cardValue[$key];
        }
        sort($point);
        if($point[count($point)-1] <=12 and ($point[count($point)-1] - $point[0]+1)==count($point))
            return true;

        return false;
    }

    public static function single($cards)
    {
        $cardValue = Config::getInstance()->getConf('card')['value'];
        $key = substr($cards[0],1,2);
        return ['type'=>'single','value'=>$cardValue[$key]];
    }

    public static function pair($cards)
    {
        $cardValue = Config::getInstance()->getConf('card')['value'];
        $key = substr($cards[0],1,2);
        return ['type'=>'pair','value'=>$cardValue[$key]];
    }

    public static function triple($cards)
    {
        $cardValue = Config::getInstance()->getConf('card')['value'];
        $key = substr($cards[0],1,2);
        return ['type'=>'triple','value'=>$cardValue[$key]];
    }

    public static function bomb($cards)
    {
        $cardValue = Config::getInstance()->getConf('card')['value'];
        $key = substr($cards[0],1,2);
        return ['type'=>'bomb','value'=>$cardValue[$key]];
    }

    public static function threeOne($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        $value = array_pop($keys);
        return ['type'=>'threeOne','value'=>$value];
    }

    public static function threePair($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        $value = array_pop($keys);
        return ['type'=>'threePair','value'=>$value];
    }

    public static function fourTwo($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        $value = array_pop($keys);
        return ['type'=>'fourTwo','value'=>$value];
    }

    public static function fourPair($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        $value = array_pop($keys);
        return ['type'=>'fourPair','value'=>$value];
    }

    public static function flight($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        return ['type'=>'flight','value'=>max($keys)];
    }

    public static function flightTwo($cards)
    {
        $value =[];
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        $value[] = array_pop($keys);
        $value[] = array_pop($keys);
        return ['type'=>'flightTwo','value'=>max($value)];
    }

    public static function flightPair($cards)
    {
        $value =[];
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        $value[] = array_pop($keys);
        $value[] = array_pop($keys);
        return ['type'=>'flightPair','value'=>max($value)];
    }

    public static function straight($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        return ['type'=>'straight','value'=>max($keys),'length'=>count($cards)];
    }

    public static function company($cards)
    {
        $times = static::cardValueTimes($cards);
        $keys = array_keys($times);
        return ['type'=>'company','value'=>max($keys),'length'=>count($cards)];
    }

    public static function cardValueTimes($cards)
    {
        $cardValue = Config::getInstance()->getConf('card')['value'];
        $cardList = [];
        foreach ($cards as $card)
        {
            $cardList[]= $cardValue[substr($card,1,2)];
        }
        $times = array_count_values($cardList);
        asort($times);
        return $times;
    }

    public static function compare($thisCard,$lastCard)
    {
        if($thisCard['type'] == 'bomb' and $lastCard['type'] != 'bomb')
            return true;

        if($thisCard['type'] == $lastCard['type'])
        {
            if(in_array($thisCard['type'],['straight','company']))
            {
                if($thisCard['length'] == $lastCard['length'] and $thisCard['value'] > $lastCard['value'])
                    return true;
                else
                    return false;
            }
            else
            {
                if($thisCard['value'] > $lastCard['value'])
                    return true;
                else
                    return false;
            }
        }

        return false;
    }

}