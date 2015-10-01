<?php
header('Content-Type: application/json; charset=UTF-8');
/**
*
*/

class Controller_V2_Web_Base extends Controller
{
    /*
    public static function output_success($specific_data)
    {
        $api_data = array(
            'version'   => 2.0,
            'code'      => 100,
            'subject'   => '[OK]',
            'message'   => 'Success!',
            'data'      => $specific_data
        );

        self::output_json($api_data);
    }

    public static function output_validation_error($key, $message)
    {
        $api_data = array(
            'version'   => 2.0,
            'code'      => 301,
            'subject'   => "[ERROR] $key Validation Error!",
            'message'   => "$message",
            'data'      => array()
        );

        self::output_json($api_data);
    }

    public static function output_error($error_code)
    {
        $api_data = array(
            'version'   => 2.0,
            'code'      => $error_code,
            'subject'   => Model_V2_Error::get_subject($error_code),
            'message'   => Model_V2_Error::get_message($error_code),
            'data'      => array()
        );

        self::output_json($api_data);
    }
    */

  	public static function output_json($api_data)
  	{
  		$json = json_encode(
  			$api_data,
  			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
  		);

  		echo $json;
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

    // Not JWT
    public static function unauth()
    {
      $api_data = array(
          'api_version'   => "2.0",
          'api_code'      => 1,
          'api_message'   => "Not Authorization",
          'api_data'      => $obj = new stdClass()
      );

      $status = json_encode(
              $api_data,
              JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
      );
      echo $status;
      exit();
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
}
