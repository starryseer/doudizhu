<?php


namespace App\WebSocket\Dao;

use App\WebSocket\Model\UserModel;
use EasySwoole\ORM\DbManager;
class UserDao
{
    use \EasySwoole\Component\CoroutineSingleTon;

    public function userByAccount($account)
    {
        return DbManager::getInstance()->invoke(function ($client)use($account){

            $userModel = UserModel::invoke($client);
            $userModel = $userModel->where('account',$account);
            $user = $userModel->get();
            if(empty($user))
                return [];
            else
                return $user->toArray();
        });
    }
}