<?php


namespace App\WebSocket\Common;


class Token
{
    public static function generateToken($user)
    {
        try{
            $str = '';
            $str.= $user['id'].'-';
            $str.= time();
            return md5($str);
        }
        catch(\Exception $e)
        {
            return false;
        }
    }
}