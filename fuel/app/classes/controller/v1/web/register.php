<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);

class Controller_V1_Web_Register extends Controller
{
	public function action_sign_up()
	{
		// username/password アカウント新規登
		$username = Input::post('username');
		$password = Input::post('password');
		$os = "Web";
		// 端末ID
		// $register_id = Input::post('register_id');
		
		// コグニートID
		$identity_id = 0;
		
		// validation
		if (empty($username) && empty($password)) {
		    
		    exit;
		}
		
		// 登録処理
		try
		{
		        // users table insert
			$pass = password_hash($pass, PASSWORD_BCRYPT);
			// Model_User::create_user(
			// $username, ,$profile_img, $pass
			// );
			// self::success($keyword);

			// devices table insert

		}
		catch(\Database_Exception $e)
		{
			// self::failed($keyword);
			// error_log($e);
		}

	}
}
