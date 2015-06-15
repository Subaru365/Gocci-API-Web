<?php

class Model_User extends Model
{
    protected static $_table_name = 'users';

    public static function put_data()
    {

    }

    public static function get_auth($username, )
    {
    	$status_ary = array(
                        'username'  => "$username",
                        'picture'   => 'OK',
                        'background'=> '',
                        'badge_num' => '0',
                        'message'   => '作成完了！Gocciへようこそ！'
                        );
    }
}
