<?php


namespace App\WebSocket\Model;

use EasySwoole\ORM\AbstractModel;

class UserModel extends AbstractModel
{
    /**
     * @var string
     */
    protected $tableName = 'game_user';

    // 都是非必选的，默认值看文档下面说明
    protected $autoTimeStamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
}