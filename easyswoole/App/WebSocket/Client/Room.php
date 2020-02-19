<?php


namespace App\WebSocket\Client;

use App\WebSocket\Service\GameService;
use App\WebSocket\Service\RobService;
use App\WebSocket\Service\UserService;
use App\WebSocket\Service\RoomService;
use EasySwoole\EasySwoole\Config;

class Room extends Base
{
    public function create()
    {
        $content = $this->caller()->getArgs();
        if(!isset($content['token']) or !isset($content['id']) or !isset($content['times']) or !in_array($content['times'],[1,2,3,4]) or !isset($content['callBackIndex']))
        {
            $this->response()->setMessage($this->jsonReturn(49999,[],'缺少参数'));
            return;
        }

        if(empty($user = UserService::getInstance()->accessUser($content['id'],$content['token'],$this->caller()->getClient()->getFd())))
        {
            $this->response()->setMessage($this->jsonReturn(50001,[],'身份验证失败',$content['callBackIndex']));
            return;
        }

        $conf = Config::getInstance()->getConf('room')[$content['times']];
        if($user['gold'] < $conf['needGold'])
        {
            $this->response()->setMessage($this->jsonReturn(50002,[],'金币不足',$content['callBackIndex']));
            return;
        }

        if(empty($room = RoomService::getInstance()->createRoom($user,$conf)))
        {
            $this->response()->setMessage($this->jsonReturn(50003,[],'创建失败',$content['callBackIndex']));
            return;
        }

        $this->response()->setMessage($this->jsonReturn(200,['route'=>'room.create','room'=>$room],'',$content['callBackIndex']));
    }

    public function join()
    {
        $content = $this->caller()->getArgs();
        if(!isset($content['token']) or !isset($content['id']) or !isset($content['roomId']) or !isset($content['callBackIndex']))
        {
            $this->response()->setMessage($this->jsonReturn(49999,[],'缺少参数'));
            return;
        }

        if(empty($user = UserService::getInstance()->accessUser($content['id'],$content['token'],$this->caller()->getClient()->getFd())))
        {
            $this->response()->setMessage($this->jsonReturn(50001,[],'身份验证失败',$content['callBackIndex']));
            return;
        }

        $room = RoomService::getInstance()->joinRoom($user,$content['roomId']);
        if(empty($room))
        {
            $this->response()->setMessage($this->jsonReturn(50002,[],'房间已满',$content['callBackIndex']));
            return;
        }

        $this->response()->setMessage($this->jsonReturn(200,['route'=>'room.join','room'=>$room],'',$content['callBackIndex']));
        $fds = RoomService::getInstance()->getOtherFds($user['id'],$content['roomId']);
        $user = RoomService::getInstance()->getUser($user['id'],$content['roomId']);
        $this->notifyFds($fds,$this->jsonReturn(201,['route'=>'room.otherJoin','user'=>$user]));

    }

    public function ready()
    {
        $content = $this->caller()->getArgs();
        if(!isset($content['token']) or !isset($content['id']) or !isset($content['roomId']) or !isset($content['callBackIndex']))
        {
            $this->response()->setMessage($this->jsonReturn(49999,[],'缺少参数'));
            return;
        }

        if(empty($user = UserService::getInstance()->accessUser($content['id'],$content['token'],$this->caller()->getClient()->getFd())))
        {
            $this->response()->setMessage($this->jsonReturn(50001,[],'身份验证失败',$content['callBackIndex']));
            return;
        }

        if(empty($user = RoomService::getInstance()->readyRoom($user['id'],$content['roomId'])))
        {
            $this->response()->setMessage($this->jsonReturn(50002,[],'用户准备失败',$content['callBackIndex']));
            return;
        }

        $this->response()->setMessage($this->jsonReturn(200,['route'=>'room.ready','user'=>$user],'',$content['callBackIndex']));
        $fds = RoomService::getInstance()->getOtherFds($user['id'],$content['roomId'],false);
        $this->notifyFds($fds,$this->jsonReturn(201,['route'=>'room.otherReady','user'=>$user]));

        if(RoomService::getInstance()->allReady($content['roomId']))
        {
            $game = GameService::getInstance()->init($content['roomId']);
            foreach ($fds as $p =>$fd) {
                $this->notifyFd($fd,$this->jsonReturn(201,['route'=>'room.start','card'=>json_decode($game['c'.$p],true)]));
            }

            $robUser = RobService::getInstance()->robUser($content['roomId']);
            $this->notifyFds($fds,$this->jsonReturn(201,['route'=>'rob.turn','user'=>$robUser]));
        }

    }
}