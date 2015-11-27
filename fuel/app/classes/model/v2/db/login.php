<?php

/**
 * Login DB Model
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V2_DB_Login extends Model
{
    use GocciAPI:

    private static $_table_name = 'logins';

    public static function postLogin($user_id)
    {
        $result = DB::insert('logins')
        ->set(array(
            'login_user_id' => $user_id
        ))
        ->execute();
    }
}