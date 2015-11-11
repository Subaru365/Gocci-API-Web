<?php

/**
 * Login Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Login extends Model
{
    /**
     * 1投稿のgochi数を求める
     * @param Int $user_id
     */
    public static function post_login($user_id)
    {
        $result = DB::insert('logins')
        ->set(array('login_user_id' => "$user_id"))
        ->execute();
    }
}
