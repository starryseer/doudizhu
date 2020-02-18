<?php
/**
 * Created by PhpStorm.
 * User: Apple
 * Date: 2018/11/1 0001
 * Time: 14:42
 */
namespace App\WebSocket\Client;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\AbstractInterface\Controller;

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class Base extends Controller
{
    public function jsonReturn($code=200,$data=[],$message='',$callBackIndex='')
    {
        $json = ['code'=>$code,'data'=>$data,'message'=>$message];
        if(!empty($callBackIndex))
            $json['callBackIndex'] = $callBackIndex;
        return json_encode($json,true);
    }

    public function push($fd,$data)
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $server->push($fd,$data);
    }

    public function asyncPush($fd,$data)
    {
        \EasySwoole\EasySwoole\Swoole\Task\TaskManager::async(function ()use($fd,$data) {
            $server = ServerManager::getInstance()->getSwooleServer();
            $server->push($fd,$data);
        });
    }

    public function notifyFds($fds,$data)
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        foreach($fds as $fd)
        {
            $server->push($fd,$data);
        }
    }

    public function notifyFd($fd,$data)
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $server->push($fd,$data);

    }
}