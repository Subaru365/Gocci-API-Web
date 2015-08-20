<?php
/**
 * 
 *
 */

class Controller_V1_Web_Base extends Controller
{
	// jwt check
	public function before()
	{
		$jwt = @$_SERVER["HTTP_AUTHORIZE"] ?  @$_SERVER["HTTP_AUTHORIZE"] : "見つからない！！！";
		echo "jwt token:" . $jwt;
		// ブラウザの全てのHTTPリクエストヘッダを取得する(token取得)
		// $headers = self::getallheaders();
		// print_r($headers);

        // headerからJWTを取り出す$jwtに代入する
		// $jwt = $headers['token'];
		// 
		// $jwt = self::decode($jwt);
		// var_dump($jwt);

		// user_idもしくはusernameを取得
		
 		// decode
 		// 
		/*
		if($jwt)
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

	public static function output_json($data)
	{
		$json = json_encode(
			$data,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);

		echo "$json";
	}

	public static function getallheaders()
	{
		$headers = '';
	    foreach ($_SERVER as $name => $value)
	    {
	    	print_r($_SERVER);
	        if (substr($name, 0, 5) == 'HTTP_')
	        {
	             $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	        }
	    }
	    return $headers;
	}

}