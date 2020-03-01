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

        while(count($pos) <3)
        {
            $pos[] = count($pos) + 17;
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

    public static function tipCard($card,$lastCard)
    {
        if(empty($lastCard))
        {
            $tipCard = static::tipSingle($card,['type'=>'single','value'=>0]);
            $tipCard = array_values($tipCard);
            return $tipCard;
        }

        switch($lastCard['type'])
        {
            case 'single':
                $tipCard1 =  array_values(static::tipSingle($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'pair':
                $tipCard1 =  array_values(static::tipPair($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'triple':
                $tipCard1 =  array_values(static::tipTriple($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'bomb':
                $tipCard =  static::tipBomb($card,$lastCard);
                $tipCard = array_values($tipCard);
                return $tipCard;
            case 'threeOne':
                $tipCard1 =  array_values(static::tipThreeOne($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'threePair':
                $tipCard1 =  array_values(static::tipThreePair($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'fourTwo':
                $tipCard1 =  array_values(static::tipFourTwo($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'fourPair':
                $tipCard1 =  array_values(static::tipFourPair($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'flight':
                $tipCard1 =  array_values(static::tipFlight($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'flightTwo':
                $tipCard1 =  array_values(static::tipFlightTwo($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'flightPair':
                $tipCard1 =  array_values(static::tipFlightPair($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'straight':
                $tipCard1 =  array_values(static::tipStraight($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            case 'company':
                $tipCard1 =  array_values(static::tipCompany($card,$lastCard));
                $tipCard2 = array_values(static::tipBomb($card,$lastCard));
                $tipCard = array_values(array_merge($tipCard1,$tipCard2));
                return $tipCard;
            default:
                break;
        }
    }

    public static function tipSingle($card,$lastCard)
    {
        $valueTimes = static::cardValueTimes($card);
        $tipCard = static::tipSameCompare($valueTimes,$card,$lastCard['value'],1);
        return $tipCard;
    }

    public static function tipPair($card,$lastCard)
    {
        $valueTimes = static::cardValueTimes($card);
        $tipCard = static::tipSameCompare($valueTimes,$card,$lastCard['value'],2);
        return $tipCard;
    }

    public static function tipTriple($card,$lastCard)
    {
        $valueTimes = static::cardValueTimes($card);
        $tipCard = static::tipSameCompare($valueTimes,$card,$lastCard['value'],3);
        return $tipCard;
    }

    public static function tipBomb($card,$lastCard)
    {
        $valueTimes = static::cardValueTimes($card);
        if($lastCard['type'] == 'bomb')
            $tipCard = static::tipSameCompare($valueTimes,$card,$lastCard['value'],4);
        else
            $tipCard = static::tipSameCompare($valueTimes,$card,0,4);
        $tipBomb = static::tipKingBomb($card);
        if(!empty($tipBomb))
            $tipCard[] = $tipBomb;
        return $tipCard;
    }

    public static function tipThreeOne($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $three = static::tipSameCompare($valueTimes,$card,$lastCard['value'],3);
        $one = static::tipSameCompare($valueTimes,$card,0,1);
        foreach($three as $threeValue => $threeC)
        {
            foreach($one as $oneValue => $oneC)
            {
                if($threeValue !== $oneValue)
                {
                    $tipCard[$threeValue] = array_values(array_merge($three[$threeValue],$one[$oneValue]));
                    break;
                }
            }
        }
        return $tipCard;
    }

    public static function tipThreePair($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $three = static::tipSameCompare($valueTimes,$card,$lastCard['value'],3);
        $pair = static::tipSameCompare($valueTimes,$card,0,2);
        foreach($three as $threeValue => $threeC)
        {
            foreach($pair as $pairValue => $pairC)
            {
                if($threeValue !== $pairValue)
                {
                    $tipCard[$threeValue] = array_values(array_merge($three[$threeValue],$pair[$pairValue]));
                    break;
                }
            }
        }
        return $tipCard;
    }

    public static function tipFourTwo($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $four = static::tipSameCompare($valueTimes,$card,$lastCard['value'],4);
        $one = static::tipSameCompare($valueTimes,$card,0,1);
        foreach($four as $fourValue => $fourC)
        {
            $ones = [];
            foreach($one as $oneValue => $oneC)
            {
                if($fourValue !== $oneValue)
                {
                    $ones = array_values(array_merge($ones,$oneC));
                }
                if(count($ones) == 2)
                {
                    $tipCard[$fourValue] = array_values(array_merge($four[$fourValue],$ones));
                    break;
                }
            }
        }
        return $tipCard;
    }

    public static function tipFourPair($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $four = static::tipSameCompare($valueTimes,$card,$lastCard['value'],4);
        $pair = static::tipSameCompare($valueTimes,$card,0,2);
        foreach($four as $fourValue => $fourC)
        {
            $ones = [];
            foreach($pair as $pairValue => $pairC)
            {
                if($fourValue !== $pairValue)
                {
                    $ones = array_values(array_merge($ones,$pairC));
                }
                if(count($ones) == 4)
                {
                    $tipCard[$fourValue] = array_values(array_merge($four[$fourValue],$ones));
                    break;
                }
            }
        }
        return $tipCard;
    }

    public static function tipKingBomb($card)
    {
        $king =  array_intersect($card,['M0','M1']);
        if(count($king) == 2)
            return $king;
        else
            return [];
    }

    public static function tipFlight($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $three = static::tipSameCompare($valueTimes,$card,$lastCard['value']-1,3,true);
        $threeValues = array_keys($three);
        foreach($three as $threeValue => $threeC)
        {
            if(in_array($threeValue-1,$threeValues))
                $tipCard[$threeValue] = array_values(array_merge($threeC,$three[$threeValue -1]));
        }
        return $tipCard;
    }

    public static function tipFlightTwo($card,$lastCard)
    {
        $tipCard = [];
        $flight = [];
        $valueTimes = static::cardValueTimes($card);
        $three = static::tipSameCompare($valueTimes,$card,$lastCard['value']-1,3,true);
        $one = static::tipSameCompare($valueTimes,$card,0,1);
        $threeValues = array_keys($three);
        foreach($three as $threeValue => $threeC)
        {
            if(in_array($threeValue-1,$threeValues))
            {
                $flight[$threeValue] = array_values(array_merge($threeC,$three[$threeValue -1]));
                $ones = [];
                foreach($one as $oneValue => $oneC)
                {

                    if(!in_array($oneValue,[$threeValue,$threeValue-1]))
                    {
                        $ones = array_values(array_merge($ones,$oneC));
                    }
                    if(count($ones) == 2)
                    {
                        $tipCard[$threeValue] = array_values(array_merge($flight[$threeValue],$ones));
                        break;
                    }
                }
            }

        }

        return $tipCard;
    }

    public static function tipFlightPair($card,$lastCard)
    {
        $tipCard = [];
        $flight = [];
        $valueTimes = static::cardValueTimes($card);
        $three = static::tipSameCompare($valueTimes,$card,$lastCard['value']-1,3,true);
        $one = static::tipSameCompare($valueTimes,$card,0,2);
        $threeValues = array_keys($three);
        foreach($three as $threeValue => $threeC)
        {
            if(in_array($threeValue-1,$threeValues))
            {
                $flight[$threeValue] = array_values(array_merge($threeC,$three[$threeValue -1]));
                $ones = [];
                foreach($one as $oneValue => $oneC)
                {

                    if(!in_array($oneValue,[$threeValue,$threeValue-1]))
                    {
                        $ones = array_values(array_merge($ones,$oneC));
                    }
                    if(count($ones) == 4)
                    {
                        $tipCard[$threeValue] = array_values(array_merge($flight[$threeValue],$ones));
                        break;
                    }
                }
            }

        }

        return $tipCard;
    }

    public static function tipStraight($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $one = static::tipSameCompare($valueTimes,$card,$lastCard['value']-$lastCard['length']+1,1,true);
        $oneValues = array_keys($one);
        foreach($one as $oneValue => $oneC)
        {
            $temp = range($oneValue - $lastCard['length']+1,$oneValue);
            if(count(array_intersect($temp,$oneValues)) == $lastCard['length']) {
                $tipCard[$oneValue] = [];
                foreach($temp as $v)
                {
                    $tipCard[$oneValue] = array_merge($tipCard[$oneValue],$one[$v]);
                }

            }
        }
        return $tipCard;
    }

    public static function tipCompany($card,$lastCard)
    {
        $tipCard = [];
        $valueTimes = static::cardValueTimes($card);
        $one = static::tipSameCompare($valueTimes,$card,$lastCard['value']-$lastCard['length']/2+1,2,true);
        $oneValues = array_keys($one);
        foreach($one as $oneValue => $oneC)
        {
            $temp = range($oneValue - $lastCard['length']/2+1,$oneValue);
            if(count(array_intersect($temp,$oneValues)) == $lastCard['length']/2) {
                $tipCard[$oneValue] = [];
                foreach($temp as $v)
                {
                    $tipCard[$oneValue] = array_merge($tipCard[$oneValue],$one[$v]);
                }

            }
        }
        return $tipCard;
    }

    public static function tipSameCompare($valueTimes,$card,$min,$length,$top= false)
    {
        $tipCard = [];
        $point = Config::getInstance()->getConf('card')['value'];
        foreach($valueTimes as $value =>$times)
        {
            if($value > $min and $times >= $length)
            {
                if($top and $value >=13)
                    continue;

                $tipCard[$value] = [] ;
            }

        }

        if(count($tipCard)>0)
        {
            $pointKeys = array_keys($tipCard);
            foreach($card as $c)
            {
                if(in_array($point[substr($c,1,2)],$pointKeys) and count($tipCard[$point[substr($c,1,2)]]) < $length)
                {
                    $tipCard[$point[substr($c,1,2)]][] = $c;
                }
            }
        }
        ksort($tipCard);
        return $tipCard;
    }



}