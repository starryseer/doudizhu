<?php


namespace App\WebSocket\Service;

use App\WebSocket\Dao\UserDao;
use App\WebSocket\Common\Token;
use App\WebSocket\Cache\UserCache;
use App\WebSocket\Cache\FdCache;

class UserService
{
    use \EasySwoole\Component\CoroutineSingleTon;

    public function login($account,$password,$fd)
    {
        $userDao = UserDao::getInstance();
        $user = $userDao->userByAccount($account);
        if(empty($user) or $user['password'] != md5($password))
            return [];

        $token = Token::generateToken($user['id']);
        if(!$token)
            return [];

        $user['token'] = $token;
        unset($user['password']);
        unset($user['account']);
        unset($user['create_at']);
        unset($user['update_at']);

        if(!UserCache::getInstance()->saveUser($user,$fd) or !FdCache::getInstance()->saveFd($fd,$user['id']))
            return [];

        return $user;
    }

    public function accessUser($id,$token,$fd)
    {
        $user = UserCache::getInstance()->getUser($id);
        if(empty($user) or $user['token'] != $token or $user['fd'] != $fd)
            return [];

        return $user;
    }
}