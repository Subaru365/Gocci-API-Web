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
header('X-Content-Type-Options: nosniff');
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
     * 
     * @var string
     */
    public static $Successful_API_request_message = "Successful API request";

    /**
     *
     * @var string
     */
    public static $Error_API_request_message = "Error_API_request";

    /**
     * 
     * @var Array
     */
    public static $base_data = [];

    /**
     * api base_data template
     *
     * @param string $api_code
     * @param string $api_message 
     * @param string $api_data
     * @param string $jwt
     *
     * @return 
     */
    public static function base_template($api_code, $api_message, $login_flag, $api_data, $jwt)
    {
	$base_data  = [
	    "api_version" => self::$Version,
	    "api_uri"     => Uri::string(),
	    "api_code"    => $api_code,
	    "api_message" => $api_message,
	    "login_flag"  => $login_flag,
	    "api_data"    => $api_data,
	    "jwt"         => $jwt
	];
	return $base_data;
    }    

    /**
     * json_encode template
     * @param Array $status
     * return $status
     */
    public static function json_encode_template($status)
    {
	$status = json_encode(
            $status,
            JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
        );
	return $status;
    }
   
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

    public static function get_jwt_token($uri="", $login_flag)
    {
        $jwt = self::get_jwt();
        if(isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);
            if (empty($obj)) {
                self::unauth();
            }
            $user_id   = $obj->{'user_id'};
            session::set('user_id', $user_id);
            $username  = $obj->{'username'};
            session::set('username', $username);
            $exp       = $obj->{'exp'};
            session::set('exp', $exp);
        } else {
            self::unauth();
            error_log('UnAuthorized Accsess..');
            exit;
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
            error_log('decodedの中身を確認 by base decode');
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
            error_log('有効期限内です. jwtを更新します');
            $jwt = self::_refresh_token();
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
        error_log('-*-*-*-*JWT was update!-*-*-*');   
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
	$status = self::json_encode_template($status);
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
	$json = self::json_encode_template($api_data);
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
    $status = [
      'message' => 'Userが存在しません'
    ];
    $status = self::json_encode_template($status);
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
    $api_data = [
      'message' => 'usernameを入力してください'
    ];
    $base_data = self::base_template($api_code = 1, $api_message = "Failed", $login_flag = 0, $api_data, $jwt = "");
    $status    = self::json_encode_template($base_data);
    echo $status;
    exit;
  }

   /**
   * Success JSON output
   *
   * @return string
   */
   // 以下のメソッドを使用していなかったら廃止 => register.phpで使用中(output_jsonに以降すべき)

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
	$status = self::json_encode_template($status);
        echo $status;
        exit;
  }
  
  public static function error_signin($message)
  {
      $status = [
          "api_version" => 3.0,
          "api_uri"     => Uri::string(),
          "api_code"    => 'ERR_SGNIN',
          "api_message" => $message,
          "login_flag"  => 0,
          "api_data"    => $obj = new stdClass()
      ];
      $status = self::json_encode_template($status);
      echo $status;
      exit;
  }

  public static function error_register($message)
  {
      $status = [
          "api_version" => 3.0,
          "api_uri"     => Uri::string(),
          "api_code"    => 'ERROR_ALREADY_REGISTER',
          "api_message" => $message,
          "login_flag"  => 0,
          "api_data"    => $obj = new stdClass()
      ];
      $status = self::json_encode_template($status);
      echo $status;
      exit;
  }


  // 以下使っていなかったら廃止
  /**
  * error json output
  *
  * @return string
  */
  public static function error_json($message)
  {
      $api_message = "UnAuthorized";
      // ver3 validation
      $status = [
          "api_version" => 3.0,
          "api_uri"     => Uri::string(),
          "api_code"    => $api_message, #"VALIDATION ERROR",
          "api_message" => $message,
          "login_flag"  => 0,
          "api_data"    => $obj = new stdClass()
      ];
      $status = self::json_encode_template($status);
      echo $status;
      exit;     
  }
  
  public static function timeline_template()
  {
	$user_id  = session::get('user_id');
    	$username = session::get('username');
	$exp      = session::get('exp');
	$jwt      = self::check_jwtExp($exp);
	$sort_key = 'all';
	$limit    = 20;

	$option   = [
		    'call'	    => Input::get('call', 0),
		    'order_id'      => Input::get('order_id', 0),
		    'category_id'   => Input::get('category_id', 0),
		    'value_id'      => Input::get('value_id', 0),
		    'lon'           => Input::get('lon', 0),
		    'lat'	    => Input::get('lat', 0)
	];
	$data = Model_Post::get_data($user_id, $sort_key, $sort_key, $limit);

	for ($i = 0; $i<$limit; $i++) {
	    $post_id = $data[$i]['post_id'];
	    $Comment_data = Model_Comment::get_data($post_id);
	    $hash_id = Hash_Id::video_hash($post_id);
	    $data[$i]['hash_id']  = $hash_id;
	    $data[$i] = [
	        "post"     => $data[$i],
		"comments" => $Comment_data
	    ];
  	}
	return $data;
  }

  /**
  * get jwt method
  * 
  * @return string $jwt
  */
  public static function get_jwt()
  {
      $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";
  
      return $jwt;
  }

  /**
  * expired token
  *
  * @return string
  */
  public static function expired_token($message)
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
	$status = self::json_encode_template($status);
        echo $status;
        exit;
  }
}
