<?php
namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use \EasySwoole\RedisPool\Redis;

class Process extends AbstractProcess
{

    protected function run($arg)
    {
//        go(function () {
//            while (true) {
//                Redis::invoke('redis', function ($redis) {
//                    var_dump($redis->keys('*'));
//                });
//                \co::sleep(10);
//            }
//        });
    }


    protected function onShutDown()
    {
    /*
    * 该回调可选
    * 当该进程退出的时候，会执行该回调
    */
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
    /*
    * 该回调可选
    * 当该进程出现异常的时候，会执行该回调
    */
    }
}