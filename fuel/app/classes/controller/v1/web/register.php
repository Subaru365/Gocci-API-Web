<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);

class Controller_V1_Web_Register extends Controller
{
	public function action_sign_up()
	{
		// username/password アカウント新規登録
		echo "register test";

		$username = Input::post('username');

		$password = Input::post('password');

		$os = Input::post('os');

		$model = Input::post('model');

		$register_id = Input::post('register_id');
		// $identity_id = "";
		// $badge_num = "";
		// $facebook_flag = "";
		// $twitter_flag = "";
		// $login_flag = "";

		// validation

		// 登録処理
		try
		{
			$pass = password_hash($pass, PASSWORD_BCRYPT);
			// Model_User::create_user(
			// $username, ,$profile_img, $pass
			// );
			// self::success($keyword);
		}
		catch(\Database_Exception $e)
		{
			// self::failed($keyword);
			// error_log($e);
		}

	}
}
