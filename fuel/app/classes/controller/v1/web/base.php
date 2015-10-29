<?php
/**
 * Base Class Api
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);
/**
 * Base Class
 * @author Kazunori Tani <k-tani@inase-inc.jp>
 *
 */
class Controller_V1_Web_Base extends Controller
{
    /**
     * The Web Gocci api Version number.
     * @var string
     */
    public static $Version = '3.0';    

    /**
    * post check
    *
    * @return string
    */
    public static function post_check()
    {
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // post  
      } else {
        // getなので不正な処理
        self::error_json("UnAuthorized");
      }
    }

    /**
    * decode
    * @param  $jwt
    * @return string
    */
    public static function decode($jwt)
    {
        $key = 'i_am_a_secret_key';
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $decoded = session::set('data', $decoded);
            error_log('decodedの中身を確認 by base');
            error_log($decoded);

        } catch (Exception $e){
            $decoded = "";
        }
        return $decoded;
    }

    /**
    * encode (JWT create)
    * @param  $user_id
    * @params $username
    * @return string
    */
    public static function encode($user_id, $username)
    {
        $key   = 'i_am_a_secret_key';           
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

    /**
    * jwt expire check
    * @params $exp
    * @return string
    */   
    public static function check_jwtExp($exp)
    {
        $jwt = "";
        if (isset($exp) && (time() >= $exp)) { 
            error_log('=jwtの有効期限切れ=');
            self::expired_token("Expired Token");
        } else {
            error_log('有効期限内です');
            // refresh_token method call!
            $jwt = self::_refresh_token();
            $time = time();
            error_log('現在時刻');
            error_log($time);
            error_log('exp');
            error_log($exp);
        }
        return $jwt;
    }

    /**
    * refresh token method
    *
    * @return string
    */
    public static function _refresh_token()
    {
        // jwt tokenのexpが有効なのでtokenの有効期限伸ばす(refreshする)
        $user_id  = session::get('user_id');
        $username = session::get('usernaem');

        // 古いSessionデータexpを破棄する
        Session::delete('exp');
        $jwt = self::encode($user_id, $username);
        error_log('JWT was update!');   
        return $jwt;
    }


    /**
    * Not JWT unauth
    * @params $uri
    * @params $login_flag
    * @return string
    */
    public static function unauth($uri="",$login_flag=0)
    {
        error_log('アクセス拒否 base unauth method.');
        $status = [
                   "api_version" => self::$Version,
                   "api_uri"     => Uri::string(),
                   "api_code"    => 1,
                   "api_message" => "UnAuthorized",
                   "login_flag"  => $login_flag,
                   "api_data"    => $obj = new stdClass()
        ];
        $status = json_encode(
            $status,
            // JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
            JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
        );
        echo $status;
        exit;
    }

    /**
    * output json
    * @param  $data
    * @return string
    */
    public static function output_json($api_data)
    {
	/*	
        $base_data = [
            "api_version" => 3.0,
            "api_uri"     => Uri::string(),
            "api_code"    => "SUCCESS",
            "api_message" => "Successful API request",
            "api_data"    => $api_data
        ];
	*/
        $json = json_encode(
                // $base_data,
		$api_data,
                JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
        );
        echo $json;
    }

    /**
    * getallheaders
    *
    * @return string
    */
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

  /**
  * UserIdが存在するかどうか
  *
  * @return string
  */
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

  /**
  * ユーザネームを入力しているかどうか
  *
  * @return string
  */
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

   /**
    * api_data_status template
    *
    *
    */
    public static function api_data_status_template()
    {
        $api_data = [

        ];

    }

 
   /**
   * Success JSON output
   *
   * @return string
   */
  // public static function success_json($api_daata) {
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
            "api_version" => 3.0,
            "api_uri"     => Uri::string(),
            "api_code"    => "SUCCESS",
            "api_message" => "$message Successful API request",
            "api_data"    => $api_data
        ];

        $status = json_encode(
                $status,
                JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
        );

        echo $status;
        exit;
  }

  /**
  * error json output
  *
  * @return string
  */
  public static function error_json($message)
  {
      // ver3 validation
      $status = [
          "api_version" => 3.0,
          "api_uri"     => Uri::string(),
          "api_code"    => " VALIDATION ERROR",
          "api_message" => $message,
          "login_flag"  => 0,
          "api_data"    => $obj = new stdClass()
      ];
      $status = json_encode(
                $status,
                JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
      );
      echo $status;
      exit;     
  }

  /**
  * expired token
  *
  * @return string
  */
  public static function expired_token()
  {
        // $login_flagが2であれば、フロント側でリダイレクトする
        $status = [
          "api_version" => 3.0,
          "api_uri"     => Uri::string(),
          "api_code"    => "Failed",
          "api_message" => $message,
          "login_flag"  => 2,
          "api_data"    => $obj = new stdClass()
        ];
        $status = json_encode(
            $status,
            JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
        );
        echo $status;
        exit;
  }
}
