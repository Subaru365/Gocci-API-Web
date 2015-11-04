<?php

class Model_Login extends Model
{
        public static function post_login($user_id)
        {
                $result = DB::insert('logins')
                ->set(array('login_user_id' => "$user_id"))
                ->execute();
        }
}
