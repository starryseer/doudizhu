<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config as DbConfig;

use EasySwoole\Socket\Dispatcher;
use App\WebSocket\WebSocketParser;
use App\WebSocket\WebSocketEvent;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        self::loadConf();
    }
    public static function loadConf()
    {
        //遍历目录中的文件
        $files = \EasySwoole\Utility\File::scanDirectory(EASYSWOOLE_ROOT . '/App/Conf');
        if (is_array($files)) {
            //$files['files'] 一级目录下所有的文件,不包括文件夹
            foreach ($files['files'] as $file) {
                $fileNameArr = explode('.', $file);
                $fileSuffix = end($fileNameArr);

                if ($fileSuffix == 'php') {
                    \EasySwoole\EasySwoole\Config::getInstance()->loadFile($file);//引入之后,文件名自动转为小写,成为配置的key
                }
            }
        }
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $config = new DbConfig();
        $config->setDatabase(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
        $config->setUser(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.user'));
        $config->setPassword(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.password'));
        $config->setHost(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.host'));
        //连接池配置
        $config->setGetObjectTimeout(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.objectTimeout')); //设置获取连接池对象超时时间
        $config->setIntervalCheckTime(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.intervalCheckTime')); //设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.maxIdleTime')); //连接池对象最大闲置时间(秒)
        $config->setMaxObjectNum(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.maxObjectNum')); //设置最大连接池存在连接对象数量
        $config->setMinObjectNum(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.minObjectNum')); //设置最小连接池存在连接对象数量

        DbManager::getInstance()->addConnection(new Connection($config));

        $poolConfig = new \EasySwoole\Pool\Config();
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS'));
        \EasySwoole\RedisPool\Redis::getInstance()->register('redis',new \EasySwoole\Redis\Config\RedisConfig());

        /**
         * **************** websocket控制器 **********************
         */
        // 创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $conf->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new Dispatcher($conf);
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });

        //自定义握手事件
        $websocketEvent = new WebSocketEvent();
        $register->set(EventRegister::onHandShake, function (\swoole_http_request $request, \swoole_http_response $response) use ($websocketEvent) {
            $websocketEvent->onHandShake($request, $response);
        });

        //自定义关闭事件
        $register->set(EventRegister::onClose, function (\swoole_server $server, int $fd, int $reactorId) use ($websocketEvent) {
            $websocketEvent->onClose($server, $fd, $reactorId);
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}