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

class Play extends Base
{
    public function play()
    {
        $content = $this->caller()->getArgs();
        var_dump($content['card']);
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

        $this->response()->setMessage($this->jsonReturn(200, [], '', $content['callBackIndex']));
        $user = $playService->nextPlayer($content['roomId']);
        $this->notifyFd($user['fd'], $this->jsonReturn(201, ['route' => 'play.turn']));
    }
}