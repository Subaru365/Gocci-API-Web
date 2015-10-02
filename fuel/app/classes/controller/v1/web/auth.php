<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');

// header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Auth api
 *
 */
class Controller_V1_Web_Auth extends Controller
{
    // ログイン
    public function action_login()
    {
        $keyword     = 'ログイン';
        $provider    = Input::get('provider');
        $token       = Input::get('token');

        try
        {
            if (empty($provider) && empty($token) || empty($provider) or empty($token) ) {
                self::error_json();
            }
            $identity_id = Model_Cognito::get_identity_id($provider, $token);
            $user_data   = Model_User::get_auth($identity_id);

            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];

            // JWT生成
            $jwt = self::encode($user_id, $username);

            Model_Login::post_login($user_id);

            self::success(
                $keyword,
                $user_id,
                $username,
                $profile_img,
                $identity_id,
                $badge_num,
                $jwt
            );
        }

        // データベース登録エラー
        catch(\Database_Exception $e)
        {
            self::failed(
                $keyword,
                $user_id,
                $username,
                $profile_img,
                $identity_id,
                $badge_num
            );

            error_log($e);
        }
    }

    // passwordログイン
    public function action_pass_login()
    {
        // username
  	$username  = Input::post('username');

  	// password
  	$password  = Input::post('password');
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

    // decode
    public static function decode()
    {
	// ログイン時
        $key = 'i_am_a_secret_key';
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            print_r($decoded);

            // user_idを取得する

            error_log('ログイン成功');

        } catch (Exception $e){
            die("[ERROR] Invalid jwt. Detail: " . $e->getMessage() . "\n");
        }
        return true;
    }

    // encode
    public static function encode($user_id, $username)
    {
    	// token create
        $key   = 'i_am_a_secret_key';
        $json  = array('user_id' => $user_id,'username' => $username);
        $token = json_encode($json);

        if ($token === NULL) {
            die("[Error]\n");
        }

        $jwt = JWT::encode($token, $key);

        return $jwt;
    }

    public static function output_json($data)
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
        echo "$json";
    }

    // DBデータ入力成功
    private static function success(
        $keyword,
        $user_id,
        $username,
        $profile_img,
        $identity_id,
        $badge_num,
        $jwt
    )
    {
        $data = [
	    'api_version' => 3,
            'code'        => 200,
	    'api_message' => "success",
	    'api_data'    => $obj = new stdClass(),
            'message'     => "$keyword" . 'しました。',
            'user_id'     => "$user_id",
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
            'jwt'         => $jwt,
        ];

        Controller_V1_Mobile_Base::output_json($data);
        session::set('user_id', $user_id);
    }

    // DBデータ入力エラー
    private static function failed(
        $keyword,
        $user_id,
        $username,
        $profile_img,
        $identity_id,
        $badge_num
    )
    {
        $data = [
            'code'        => 401,
            'message'     => "$keyword" . 'できませんでした。',
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
        ];

        Controller_V1_Mobile_Base::output_json($data);
    }
 
    private function error_json()
    {
	$data = [
		"api_version" => 3,
		"api_code" => 1,
		"api_message" => "Not Authorized.",
		"api_data" => $obj = new StdClass()
	];

	$json = json_encode(
            $data,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        echo "$json";
	exit;
    }
}
