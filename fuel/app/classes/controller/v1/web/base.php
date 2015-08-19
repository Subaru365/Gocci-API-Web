<?php
/**
 * 
 *
 */

/*
require('jwt/BeforeValidException.php');
require('jwt/ExpiredException.php');
require('jwt/SignatureInvalidException.php');
require('jwt/JWT.php');
*/

class Controller_V1_Web_Base extends Controller
{
	// jwt check
	public function before()
	{

		// ブラウザの全てのHTTPリクエストヘッダを取得する(token取得)

		// jwt(token)を持ってるか

		// 
		// $jwt = self:: 
		/*
		if(empty($jwt))
		{
			self::unauth();
			error_log('UnAuthorized Accsess..');
			exit;
		}
		*/
	}

	// Not JWT 
	private static function unauth()
	{
		$status = array(
			'code'   => '401',
			'status' => 'UnAuthorized'
		);

		$status = json_encode(
        	$status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

		echo $status;
	}

	// decode
	public static function decode()
	{
		$key = 'i_am_a_secret_key';
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
    		error_log('ログイン成功');

		} catch (Exception $e){
    		die("[ERROR] Invalid jwt. Detail: " . $e->getMessage() . "\n");
		}
        return true;
	}

	// encode
	public static function encode($username, $password)
	{
		$key   = 'i_am_a_secret_key';
		$json  = array('username' => $username,'password'=>$password);
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

}