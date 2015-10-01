<?php

class Model_V2_Db_Login extends Model
{
	public static function post_login()
	{
		$result = DB::insert('logins')
		->set(array('login_user_id' => session::get('user_id')))
		->execute();
	}
}