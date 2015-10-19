<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);

class Controller_V1_Web_Auth extends Controller_V1_Web_Base
{
    const SESSION_KEY_LOGGED_IN = 'logged-in';

    // SNSログイン(Facebook/Twitter)
    public function action_login()
    {
        $keyword     = 'ログイン';
        $provider    = Input::get('provider');
        $token       = Input::get('token');

        try
        {
            if (empty($provider) && empty($token) || empty($provider) or empty($token) ) {
                self::error_json("UnAuthorized");
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

	    // JSON出力
            $api_data = [

	    ];

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

    public static function action_logout()
    {
	// このapiの脆弱制 => このapiのURLを知っていて、user_idをpostしてしまえば、
	// そのidでログインしているユーザーがかってにログアウトされてしまうことになる
	$uri = "auth/logout";
	$user_id = Input::post('user_id');
	// ユーザデータを保持していないのであれば、強制終了
	if (empty($user_id)) {
	    // baseのerror_json呼び出し
	    self::error_json('ログインしていないためユーザーデータが存在しません');
        }

	try {
	    // ログアウトのためsessionデータ削除
	    \Session::delete(self::SESSION_KEY_LOGGED_IN);

	    $api_data = [
                    "message" => "ログアウトしました"
	    ];

            $base_data = [
                    "api_version" =>3,
		    "api_uri"     =>$uri,
                    "api_code"    =>1,
                    "api_message" =>"success",
		    "login_flag"  => 0,
                    "api_data"    => $api_data
            ];
	    // json output
	    self::output_json($base_data);
	    error_log('ログアウトしました');
       } catch (Exception $e) {

       }
    }

    // username/passwordログイン
    public function action_pass_login()
    {
  	$username  = Input::post('username');
  	$password  = Input::post('password');

	// post check
	self::post_check();

        if (empty($username) && empty($password) || empty($username) or empty($password) ) {
                self::error_json("usenameもしくはpasswordが入力されていません");	
        }

  	try {
	    if (!empty($username) && !empty($password)) {
                $user_data   = Model_User::check_pass($username, $password);
                $user_id     = $user_data[0]['user_id'];
                $profile_img = $user_data[0]['profile_img'];
                $identity_id = $user_data[0]['identity_id'];
                $badge_num   = $user_data[0]['badge_num'];

                Model_Login::post_login($user_id);
               	// JWT認証
 	        $jwt = self::encode($user_id, $username);
	   
	        $api_data = [
	   	    "user_id"     => $user_id,
		    "username"    => $username,
		    "profile_img" => $profile_img,
		    "identity_id" => $identity_id,
		    "badge_num"   => $badge_num,
		    "jwt"         => $jwt
	        ];
 	    
	        $base_data = [
		    "api_version" =>3,
	            "api_code"    =>0,
	            "api_message" =>"success",
	            "api_data"    => $api_data
	        ];

	        // JSONを返す
	        self::output_json($base_data);
	    }

  	} catch (Exception $e) {

  	    // JWT Exception

  	    // Not access
  	}
   }

   public static function output_json($data)
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT//|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
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
}
