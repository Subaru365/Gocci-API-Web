<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);

class Controller_V1_Web_Register extends Controller_V1_Web_Base
{
	/**
	 * username/password register
	 * @POST username
	 * @POST password 
	 */

	public function action_sign_up()
	{
		$keyword  = 'サインアップ';
        	$badge_num= 0;
        	$user_id  = Model_User::get_next_id();
		$username = Input::post('username');
		$password = Input::post('password');
		$os       = "Web";
		$model    = "PC";
		$register_id = $user_id; // webに端末IDは存在しない

		Controller_V1_Web_Base::post_check();
		// $username = "kaz1";
	        // $password = "1234";
		try {
		    // usernameとpasswordが両方空か
		    Model_User::check_name_pass($username, $password);
 
		    // 既に使用されていないか
		    $username = Model_User::check_name($username);

		    // usernameは空ではないか
	
		    $username = Model_User::empty_name($username);
		    
		    // usernameの文字数が制限以内か
		    $username = Model_User::format_name_check($username);
		    // usernameに不適切な文字列が含まれていないか
		  
		    // passwordは入力されているか
		    $password = Model_User::empty_password($password);

		    Model_Device::check_register_id($register_id);
		    $cognito_data = Model_Cognito::post_data($user_id);
		 
		    // コグニートID
 		    $identity_id  = $cognito_data['IdentityId'];
		    $token        = $cognito_data['Token'];
	
		     // users table insert
		    $hash_pass    = password_hash($password, PASSWORD_BCRYPT);
		    $profile_img  = Model_User::insert_data($username, $identity_id,$hash_pass);
        	    // $endpoint_arn = Model_Sns::post_endpoint($user_id, $register_id, $os);
		    $endpoint_arn = 0;
        	    Model_Device::post_data($user_id, $os, $model, $register_id, $endpoint_arn);
        	    self::success_json($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $token,$message = "success");

		}
		catch(\Database_Exception $e)
		{
		    // self::failed($keyword);
		    // error_log($e);
		}

	}
}
