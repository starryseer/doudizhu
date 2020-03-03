<?php


namespace App\WebSocket\Client;

use App\WebSocket\Service\UserService;

class Login extends Base
{
    public function login()
    {
        $content = $this->caller()->getArgs();
        if(!isset($content['account']) or !isset($content['password']) or !isset($content['callBackIndex']))
        {
            $this->response()->setMessage($this->jsonReturn(49999,[],'缺少参数'));
            return;
        }

        var_dump($content);
        if(!empty($user = UserService::getInstance()->login($content['account'],$content['password'],$this->caller()->getClient()->getFd())))
            $this->response()->setMessage($this->jsonReturn(200,['route'=>'login.login','user'=>$user],'',$content['callBackIndex']));
        else
            $this->response()->setMessage($this->jsonReturn(50001,[],'账号或密码错误',$content['callBackIndex']));
    }
}