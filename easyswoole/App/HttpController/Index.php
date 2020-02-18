<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RedisPool\Redis;

class Index extends Controller
{

    function index()
    {
        $queryBuild = new QueryBuilder();
        $queryBuild->raw("select * from game_user");
        $data = DbManager::getInstance()->query($queryBuild, true, 'default');
         Redis::invoke('redis',function ($redis){
             $redis->incrby('test', 1);
            return true;
        });
        Logger::getInstance()->log('backTest:'.json_encode($data->getResult(),true));
        $this->response()->write(json_encode($data->getResult(),true));
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}