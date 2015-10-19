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

	// username/password register api
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

	        // getであれば、UnAuthorized
		Controller_V1_Web_Base::post_check();
		
		try {
		    // usernameとpasswordが両方空か
		    Model_User::check_name_pass($username, $password);
 
		    // 既に使用されていないか
		    $username = Model_User::check_web_name($username);
		    
		    // usernameは空ではないか	
		    $username = Model_User::empty_name($username);
		    
		    // usernameの文字数が制限以内か
		    $username = Model_User::format_name_check($username);

		    Model_Device::check_register_id($register_id);
		    $cognito_data = Model_Cognito::post_data($user_id);
		 
		    // コグニートID
 		    $identity_id  = $cognito_data['IdentityId'];
		    $token        = $cognito_data['Token'];

		    // user登録		
		    $profile_img  = Model_User::insert_data($username, $identity_id,$hash_pass);
		    $endpoint_arn = 0;
        	    Model_Device::post_data($user_id, $os, $model, $register_id, $endpoint_arn);
		    // jwt生成
		    $jwt = self::encode($user_id, $username);
        	    self::success_json($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $jwt, $message = "success");

		}
		catch(\Database_Exception $e)
		{
		    // self::failed($keyword);
		    // error_log($e);
		}
	}

	// sns登録(Facebook/Twitter)
        public function action_sns_sign_up()
        {
	    // webはsns_sing_up内に、sns連携処理もある

	    /**
	     * SNS登録処理手順
	     * 1 username
	     * 2 既に登録されていないか等のエラーハンドリング
	     * 3 Facebook連携
	     * 4 JSON出力
	     */

	    $keyword     = "SNS登録";
	    $badge_num   = 0;
	    $user_id     = Model_User::get_next_id();
	    $username    = Input::post('username'); // ユーザーが希望するユーザ名
	    $profile_img = Input::post('profile_img');
	    $token       = Input::post('token');
	    $provider    = Input::post('provider'); 
            $os          = "Web";
	    $model       = "PC";
	    $register_id = $user_id; // webはregister_idが存在しない

	    // getであれば、UnAuthorized
            Controller_V1_Web_Base::post_check();

	    try {
		// usernameが既に使われていないかエラーハンドリング
		$username = Model_User::check_web_name($username);
		$username = Model_User::empty_name($username);
		$username = Model_User::format_name_check($username);

		// facebook/twitterアカウントデータをusers/devices に保存する
		Model_Device::check_register_id($register_id);
                $cognito_data = Model_Cognito::post_data($user_id);
                $identity_id  = $cognito_data['IdentityId'];
                $token        = $cognito_data['Token'];

                // users table insert           
		$profile_img  = Model_User::sns_insert_data($username, $identity_id, $profile_img);
                $endpoint_arn = 0;

		// device insert
                Model_Device::post_data($user_id, $os, $model, $register_id, $endpoint_arn);


		// SNS連携
		if ($profile_img != 'none') {
                    $profile_img = Model_S3::input($user_id, $profile_img);
                    $profile_img = Model_User::update_profile_img($user_id, $profile_img);
                }

                $identity_id = Model_User::get_identity_id($user_id);
		// identity_idを発行
		// 連携したので、flagを更新
                Model_User::update_sns_flag($user_id, $provider);
                Model_Cognito::post_sns($user_id, $identity_id, $provider, $token);

		// jwt 生成
		$jwt = self::encode($user_id, $username);
		// 正常に登録したらJSON出力
		self::success_json($keyword, $user_id, $username,
			           $profile_img, $identity_id, $badge_num,
		                   $jwt,$message = "success"
		);
	    } catch (\Database_Exception $e) {
		// 

	    }
	}
}
