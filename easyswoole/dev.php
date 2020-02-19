<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SOCKET_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
//            'max_request'=>10,
            'worker_num' => 1,
            'reload_async' => true,
            'max_wait_time'=>10
        ],
        'TASK'=>[
            'workerNum'=>1,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,


    'MYSQL'         => [
        //数据库配置
        'host'                => '127.0.0.1',
        'user'                => 'root',
        'password'             => '3166sb88',
        'database'             => 'doudizhu',
        'port'                 => '3306',

        //连接池配置需要根据注册时返回的poolconfig进行配置,只在这里配置无效
        'objectTimeout'    => 30 * 1000,//定时验证对象是否可用以及保持最小连接的间隔时间
        'intervalCheckTime'    => 30 * 1000,//定时验证对象是否可用以及保持最小连接的间隔时间
        'maxIdleTime'          => 15,//最大存活时间,超出则会每$intervalCheckTime/1000秒被释放
        'maxObjectNum'         => 160,//最大创建数量
        'minObjectNum'         => 10,//最小创建数量 最小创建数量不能大于等于最大创建
    ],

    'REDIS' => [
        'host'          => '127.0.0.1',
        'port'          => '6379',
        'auth'          => '',

        //连接池配置需要根据注册时返回的poolconfig进行配置,只在这里配置无效
        'intervalCheckTime'    => 30 * 1000,//定时验证对象是否可用以及保持最小连接的间隔时间
        'maxIdleTime'          => 15,//最大存活时间,超出则会每$intervalCheckTime/1000秒被释放
        'maxObjectNum'         => 160,//最大创建数量
        'minObjectNum'         => 10,//最小创建数量 最小创建数量不能大于等于最大创建
    ],
];
