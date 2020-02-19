<?php


namespace App\WebSocket\Client;

use App\WebSocket\Service\GameService;
use App\WebSocket\Service\RobService;
use App\WebSocket\Service\RoomService;
use App\WebSocket\Service\UserService;
use EasySwoole\Component\Timer;

class Rob extends Base
{
    public function state()
    {
        $content = $this->caller()->getArgs();
        if (!isset($content['token']) or !isset($content['id']) or !isset($content['roomId']) or !isset($content['rob']) or !isset($content['callBackIndex'])) {
            $this->response()->setMessage($this->jsonReturn(49999, [], '缺少参数'));
            return;
        }

        if (empty($user = UserService::getInstance()->accessUser($content['id'], $content['token'], $this->caller()->getClient()->getFd()))) {
            $this->response()->setMessage($this->jsonReturn(50001, [], '身份验证失败', $content['callBackIndex']));
            return;
        }

        $user = RoomService::getInstance()->getUser($content['id'], $content['roomId']);
        $rob = RobService::getInstance()->getRob($content['roomId']);

        if (!$rob or $rob['turn'][$rob['num']] != $user['p']) {
            $this->response()->setMessage($this->jsonReturn(50002, [], '错误轮次', $content['callBackIndex']));
            return;
        }

        $rob = RobService::getInstance()->addRob($content['roomId'], $rob, $content['rob']);
        if (empty($rob)) {
            $this->response()->setMessage($this->jsonReturn(50003, [], '状态添加失败', $content['callBackIndex']));
            return;
        }

        $this->response()->setMessage($this->jsonReturn(200, ['route' => 'rob.state', 'user' => $user], '', $content['callBackIndex']));
        $fds = RoomService::getInstance()->getOtherFds($user['id'], $content['roomId'], false);
        $this->notifyFds($fds, $this->jsonReturn(201, ['route' => 'rob.otherState', 'user' => $user, 'rob' => $content['rob']]));

        if (RobService::getInstance()->isEnd($rob))
        {
            $lord = RobService::getInstance()->setLord($content['roomId'],$rob);
            $cards = GameService::getInstance()->cardsBySeat($content['roomId'],$lord);
            $bottom = GameService::getInstance()->getBottom($content['roomId']);
            foreach ($fds as $p =>$fd)
            {
                if($p == $lord)
                    $this->notifyFd($fd, $this->jsonReturn(201, ['route' => 'rob.end', 'lord' => $lord,'bottom'=>$bottom,'card'=>$cards]));
                else
                    $this->notifyFd($fd, $this->jsonReturn(201, ['route' => 'rob.end', 'lord' => $lord,'bottom'=>$bottom]));
            }
            $lordFd = $fds[$lord];

            Timer::getInstance()->after(2000,function()use($lordFd){
                var_dump('in');
                $this->notifyFd($lordFd, $this->jsonReturn(201, ['route' => 'play.turn']));
            });

            return;
        }

        $robUser = RobService::getInstance()->robUser($content['roomId']);
        $this->notifyFds($fds,$this->jsonReturn(201,['route'=>'rob.turn','user'=>$robUser]));
    }
}