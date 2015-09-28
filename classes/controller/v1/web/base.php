<?php
/**
 *
 *
 */

class Controller_V1_Web_Base extends Controller
{
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

	// Not JWT
	public static function unauth()
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
		exit();
	}

	public static function output_json($data)
	{
		$json = json_encode(
			$data,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
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
}
