<?php
/**
 * Register Class Api
 * @package    Gocci-Web
 * @version    3.1 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);

class Controller_V1_Web_Register extends Controller_V1_Web_Base
{
	/**
	 * username password register
	 *
	 * @param string POST $username
	 * @param string POST $password 
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
		$register_id = $user_id; 

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

	/**
         * sns register
	 *
         * @param string POST $username
         * @param string POST $profile_img
	 * @param string POST $token
	 * @param string POST $provider
         */

	// sns登録(Facebook/Twitter)
        public function action_sns_sign_up()
        {
	    $keyword     = "SNS登録";
	    $os          = "Web";
            $model       = "PC";	
	    $badge_num   = 0;
	    $user_id     = Model_User::get_next_id();
	    $username    = Input::post('username');
	    $profile_img = Input::post('profile_img');
	    $sns_token   = Input::post('token');
	    $provider    = Input::post('provider'); 
	    $register_id = $user_id;

	    // getであれば、UnAuthorized
            Controller_V1_Web_Base::post_check();

	    try {
		// usernameが既に使われていないかエラーハンドリング
		$username = Model_User::check_web_name($username);
		$username = Model_User::empty_name($username);
		$username = Model_User::format_name_check($username);

		// facebook/twitterアカウントデータをusers/devices に保存する	
		$cognito_data = Model_Cognito::post_web_sns($user_id, $provider, $sns_token);
		$identity_id  = $cognito_data['IdentityId'];
      
      		// users table insert       
		$profile_img  = Model_User::sns_insert_data($username, $identity_id, $profile_img);
        	$endpoint_arn = 0;

		// device insert
        	Model_Device::post_data($user_id, $os, $model, $register_id, $endpoint_arn);

		error_log('provider:');
        	error_log($provider);

		// 連携したので、flagを更新
        	Model_User::update_sns_flag($user_id, $provider);
		// jwt 生成
		$jwt = self::encode($user_id, $username);
		error_log('json出力します');

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
