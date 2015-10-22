<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);
date_default_timezone_set('Asia/Tokyo');

class ValidateException extends Exception {} // バリデーションエラー
class SystemErrorException extends Exception {} // PHPエラー等

// Controller Restを継承するとError発生する
// Undefined property: Controller_V3_Web_Get::$response
// class Controller_V3_Web_Base extends Controller_Rest
class Controller_V3_Web_Base extends Controller
{
     protected $format = 'json';

    public static function post_check()
    {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	     // postであれば問題なく処理を進む  	   
	} else {
	     // getなので不正な処理
	    self::error_json("UnAuthorized");
	}
    }

    // decode
    public static function decode($jwt)
    {
        $key = 'i_am_a_secret_key';
        try {
	    // $leeway in seconds
	    // JWT::$leeway = 60; 1分
	    // JWT::$leeway = 1; // 1sec
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $decoded = session::set('data', $decoded);
	    error_log('decodedの中身を確認 by base');
	    error_log($decoded);

        } catch (Exception $e){
            $decoded = "";
        }
        return $decoded;
    }

    // encode
    public static function encode($user_id, $username)
    {
        $key   = 'i_am_a_secret_key';		
	// exp (Expiration Time): jwtが無効になる時間
	// iat (Issued At): jwtが発行された時間
	// nbf (Not Before): 現在時刻が指定した時間より前なら処理しないような時間

	// $iat = time(); // jwtを発行する時間
	// $exp = $iat + 1;
        // $exp = time() + 3600;  # 1は1秒 60 = 1分 3600は60分
	//$exp = time() + 60;
	$exp = time() + 86400; // 24h
        $json  = [
		'user_id' => $user_id,
		'exp'     => $exp,
		'username'=> $username
	];
        $json = json_encode($json);
	
        if ($json === NULL) {
            die("[Error]\n");
        }
        $jwt = JWT::encode($json, $key);

        return $jwt;
    }
   
    public static function check_jwtExp($exp)
    {
	if (isset($exp) && (time() >= $exp)) { 
	    error_log('=jwtの有効期限切れ=');
	    self::expired_token("Expired Token");
	} else {
	    error_log('有効期限内です');
	    $time = time();
	    error_log('現在時刻');
	    error_log($time);
	    error_log('exp');
	    error_log($exp);
	}
    }

    // Not JWT
    public static function unauth($uri="",$login_flag=0)
    {
        error_log('アクセス拒否 base unauth method.');
	$status = [
 		   "api_version" => 3,
	           "api_uri"     => $uri,
    		   "api_code"    => 1,
    	           "api_message" => "UnAuthorized",
		   "login_flag"  => $login_flag,
	           "api_data"   => $obj = new stdClass()
	];
	$status = json_encode(
            $status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
	echo $status;
	exit;
    }

    public static function output_json($data)
    {
	$json = json_encode(
		$data,
		JSON_PRETTY_PRINT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
	);
	echo $json;
    }

    public static function getallheaders()
    {
        $headers = '';
	foreach ($_SERVER as $name => $value)
	{
	    if (substr($name, 0, 5) == 'HTTP_')
	    {
	        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	    }
	}
	return $headers;
    }

  public static function notfounduser()
  {
    $status = array(
      'code'   => '401',
      'status' => 'Userが存在しません'
    );
    $status = json_encode(
      $status,
        JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
      );
      echo $status;
      exit;
  }

  public static function notid()
  {
    $status = array(
      'code'   => '401',
      'status' => 'usernameを入力してください'
    );

    $status = json_encode(
      $status,
        JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
      );
      echo $status;
      exit;
  }

  public static function success_json($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $token,$message) 
  {
	$api_data = [
	     "user_id"     => $user_id,
	     "username"    => $username,
	     "profile_img" => $profile_img, 
	     "identity_id" => $identity_id,
	     "badge_num"   => $badge_num,
	     "jwt"         => $token,
	     "login_flag"  => 1 
	];

	$status = [
	    "api_version" => 3,
            "api_code"    => 1,
	    "api_uri"     => "",
            "api_message" => $message,
            "api_data"   => $api_data
	];

	$status = json_encode(
                $status,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        echo $status;
        exit();
  }

  public static function error_json($message)
  {
      // ver3 validation
      $status = [
          "api_version" => 3,
	  "api_uri"     => "",
          "api_code"    => 1,
          "api_message" => $message,
          "login_flag"  => 0,
          "api_data"   => $obj = new stdClass()
      ];
      $status = json_encode(
                $status,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
      );
      echo $status;
      exit();	
  }

  public static function expired_token()
  {
	$status = [
	    "api_version" => 3,
	    "api_uri"     => "",
	    "api_code"    => 1,
	    "api_message" => $message,
	    "login_flag"  => 2, // 2 リダイレクト
	    "api_data"    => $obj = new stdClass()
	];
	$status = json_encode(
	    $status,
	    JSON_PRETY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
	);
	echo $status;
	exit;
  }
}
