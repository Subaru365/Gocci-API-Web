<?php
/**
 * Auth api
 *
 * version 2.0
 *
 */

class Controller_V2_Web_Auth extends Controller
{
    private static function get_input()
    {
        $user_data = array_merge (Input::get(), Input::post());
        return $user_data;
    }


    public static function action_signup()
    {
        //Input $user_data is [username, os, model, register_id]
        $user_data = self::get_input();

        Model_V2_Validation::check_signup($user_data);

        $user_data = Model_v2_Router::create_user($user_data);

        Controller_V2_Mobile_Base::output_success($user_data);
    }

    // 通常login
    // ver1でloginだったのが、ver2ではsns_loginへ変更。
    public static function action_login()
    {
        // Input $user_data is [identity_id]
        $user_data = self::get_input();

        Model_V2_Validation::check_login($user_data);

        $user_data = Model_V2_Router::login($user_data['identity_id']);

        Controller_V2_Mobile_Base::output_success($user_data);
    }

    // sns login
    public static function action_sns_login()
    {
        //Input $user_data is [identity_id, os, model, register_id]
        $user_data = self::get_input();

        Model_V2_Validation::check_sns_login($user_data);

        $user_data = Model_V2_Router::login($user_data['identity_id']);

        Controller_V2_Mobile_Base::output_success($user_data);
    }

    /*
    public static function action_pass_login()
    {
        //Input $user_data is [username, pass, os, model, register_id]
        $user_data = self::get_input();

        Model_V2_Validation::check_pass_login($user_data);

        $user_data['identity_id'] = Model_V2_Db_User::get_identity_id($user_data['username']);

        $user_data = Model_V2_Router::login($user_data['identity_id']);

        Controller_V2_Mobile_Base::output_success($user_data);
    }
    */

    // passwordログイン
  	public function action_pass_login()
  	{
  		// username
  		$username  = Input::post('username');

  		// password
  		$password  = Input::post('password');
      // passwordが空ではないか、文字数制限は出来ているか、その他チェックする

      // validation okであれば、hash化する。
  		$hash_pass = password_hash($password, PASSWORD_BCRYPT);

  		try {
  			// JWT認証
        // usernameとpasswordの場合のtokenを作り、
        // pass_loginの際にこの2つの組み合わせと一致するUser情報があればログインする。
  			$jwt = self::encode($username, $hash_pass);

  			// sucess

  		} catch (Exception $e) {
  			// JWT Exception

  			// Not access

  		}
  	}


    public static function action_device_refresh()
    {
        //Input $user_data is [$register_id]
        $user_data = self::get_input();

        // $old_endpoint_arn = Model_Device::get_arn($user_id);
        // Model_Sns::delete_endpoint($old_endpoint_arn);

        // $new_endpoint_arn = Model_Sns::post_endpoint($user_id, $register_id, $os);
        // Model_Device::update_data($user_id, $os, $model, $register_id, $new_endpoint_arn);

    }

    // decode
    public static function decode($jwt)
    {
        $key = 'i_am_a_secret_key';
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $decoded = session::set('data', $decoded);
            // error_log('ログイン成功');

        } catch (Exception $e){
            $decoded = "";
        }
        return $decoded;
    }

    // encode
    public static function encode($user_id, $username)
    {
        $key   = 'i_am_a_secret_key';
        $json  = array('user_id' => $user_id,'username' => $username);
        $token = json_encode($json);

        if ($token === NULL) {
            die("[Error]\n");
        }

        $jwt = JWT::encode($token, $key);

        return $jwt;
    }
}
