<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);
date_default_timezone_set('Asia/Tokyo');

/**
 * base
 *
 */

class Controller_V1_Web_Base extends Controller
{
    public static function post_check()
    {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	     // postであれば問題なく処理を進む
	     // echo 'postです';
	     
	} else {
	     // getなので不正な処理と見なす
	    self::error_json("UnAuthorized");
	}
    }

    // decode
    public static function decode($jwt)
    {
        $key = 'i_am_a_secret_key';
        try {
	    // $leeway in seconds
	    // JWT::$leeway = 60;
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
		
	// exp (Expiration Time): jwtが無効になる時間
	// iat (Issued At): jwtが発行された時間
	// nbf (Not Before): 現在時刻が指定した時間より前なら処理しないような時間

	$iat = time(); // jwtを発行する時間
	$exp = $iat + 1;

        $json  = [
		'user_id' => $user_id,
		'username'=> $username,
		'iat'     => $iat,
		'exp'     => $exp,
		'nbf'     => '',
	];
        $json = json_encode($json);
	
        if ($json === NULL) {
            die("[Error]\n");
        }

        $jwt = JWT::encode($json, $key);

        return $jwt;
	// return $json;
    }

	// Not JWT
	public static function unauth()
	{
		$status = [
 		   "api_version" => 3,
    		   "api_code" => 1,
    	           "api_message" => "UnAuthorized",
	           "api_data:" => $obj = new stdClass()
		];

		$status = json_encode(
        	$status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

		echo $status;
		exit();
	}

	public static function output_json($data)
	{
		/*
		// 脆弱性あり
		$json = json_encode(
			$data,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);
		*/
		
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
	    	// print_r($_SERVER);
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
	     "user_id"  => $user_id,
	     "username" => $username,
	     "profile_img" => $profile_img, 
	     "identity_id" => $identity_id,
	     "badge_num"   => $badge_num,
	     "token"    => $token
	];

	$status = [
	    "api_version" => 3,
            "api_code" => 1,
            "api_message" => $message,
            "api_data:" => $api_data
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
                   "api_code" => 1,
                   "api_message" => $message,
                   "api_data:" => $obj = new stdClass()
                ];

                $status = json_encode(
                $status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        echo $status;
        exit();	
  }
}
