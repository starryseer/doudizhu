<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2020/2/19
 * Time: 15:51
 */

namespace App\WebSocket\Client;

use App\WebSocket\Service\RoomService;
use App\WebSocket\Service\UserService;
use App\WebSocket\Service\PlayService;
use App\WebSocket\Common\Card;

class Play extends Base
{
    public function play()
    {
        $content = $this->caller()->getArgs();
        if(!isset($content['id']) or !isset($content['token']) or !isset($content['roomId']) or  !isset($content['push']) or !isset($content['card']))
        {
            $this->response()->setMessage($this->jsonReturn(49999,[],'缺少参数'));
            return;
        }

        if (empty($user = UserService::getInstance()->accessUser($content['id'], $content['token'], $this->caller()->getClient()->getFd()))) {
            $this->response()->setMessage($this->jsonReturn(50001, [], '身份验证失败', $content['callBackIndex']));
            return;
        }

        $user = RoomService::getInstance()->getUser($user['id'],$content['roomId']);
        if(empty($user))
        {
            $this->response()->setMessage($this->jsonReturn(50002, [], '房间无此玩家', $content['callBackIndex']));
            return;
        }


        $playService = PlayService::getInstance();
        if(!$playService->isTurn($content['roomId'],$user['p']))
        {
            $this->response()->setMessage($this->jsonReturn(50003, [], '其他玩家出牌中', $content['callBackIndex']));
            return;
        }
        $lastCard = $playService->lastCard($content['roomId']);
        if(empty($lastCard) and !$content['push'])
        {
            $this->response()->setMessage($this->jsonReturn(50004, [], '必须出牌', $content['callBackIndex']));
            return;
        }



        $otherFds = RoomService::getInstance()->getOtherFds($user['id'],$content['roomId']);
        if(!$content['push'])
        {
            $playService->setLastCard($content['roomId'],[]);
            $this->response()->setMessage($this->jsonReturn(200, ['route' => 'play.play','push'=>0,'card'=>[]], '', $content['callBackIndex']));
            $this->notifyFds($otherFds,$this->jsonReturn(201,['route' => 'play.otherPlay','push'=>0,'card'=>[],'p'=>$user['p']]));
            $user = $playService->nextPlayer($content['roomId']);
            $tipCard = PlayService::getInstance()->tipCard($content['roomId'],$user['p']);
            $this->notifyFd($user['fd'], $this->jsonReturn(201, ['route' => 'play.turn','tipCard'=>$tipCard]));
            return ;
        }

        if(!$playService->hasCard($content['roomId'],$user['p'],$content['card']))
        {
            $this->response()->setMessage($this->jsonReturn(50005, [], '卡牌错误', $content['callBackIndex']));
            return;
        }

        if(max(array_count_values($content['card'])) >=2 or empty($thisCard = Card::cardType($content['card'])))
        {
            $this->response()->setMessage($this->jsonReturn(50006, [], '牌型错误', $content['callBackIndex']));
            return;
        }

        if(!empty($lastCard) and !Card::compare($thisCard,$lastCard))
        {
            $this->response()->setMessage($this->jsonReturn(50007, [], '牌大小不足', $content['callBackIndex']));
            return;
        }

        if(empty($cards = $playService->removeCard($content['roomId'],$user['p'],$content['card'])))
        {
            $this->response()->setMessage($this->jsonReturn(50008, [], '卡牌移除失败', $content['callBackIndex']));
            return;
        }

        $playService->setLastCard($content['roomId'],$thisCard);
        $this->response()->setMessage($this->jsonReturn(200, ['route' => 'play.play','push'=>1,'card'=>$cards], '', $content['callBackIndex']));

        $this->notifyFds($otherFds,$this->jsonReturn(201,['route' => 'play.otherPlay','push'=>1,'card'=>$cards,'p'=>$user['p']]));
        $user = $playService->nextPlayer($content['roomId']);
        $tipCard = PlayService::getInstance()->tipCard($content['roomId'],$user['p']);
        $this->notifyFd($user['fd'], $this->jsonReturn(201, ['route' => 'play.turn','tipCard'=>$tipCard]));
    }
}