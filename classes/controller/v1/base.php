<?php
/**
 * Base Class
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

class Controller_V1_Base extends Controller
{
    /**
     * @var Array $base_data
     */
    public static $base_data = [];

    /**
     * @var String $status
     */
    public static $status;

    /**
     * @var Int $user_id
     */
    public static $user_id;

    /**
     * @var Object $jwt_obj
     */
    // private $jwt_obj;
    public static $jwt_obj;

    public static $obj;

    /**
     * @var String $token
     */
    public static $token;

    /**
     * @var String $image
     */
    public static $image;

    const API_VERSION                     = "3.0";
    const ENV_PRO                         = "PRODUCTION";
    const ENV_DEV                         = "DEVELOPMENT";
    const SUCCESSFUL_API_REQUEST_MESSAGE  = "SUCCESSFUL_API_REQUEST_MESSAGE";
    const ERROR_API_REQUEST_MESSAGE       = "ERROR_API_REQUEST_MESSAGE";
    const API_MESSAGE_UNAUTHORIZED        = "UnAuthorized";
    const API_CODE_ERROR_ALREADY_REGISTER = "ERROR_ALREADY_REGISTER";
    const REQUEST_URL                     = 'https://api.twitter.com/oauth/access_token';
    const PROVIDER_TWITTER                = 'api.twitter.com';
    const API_KEY_TEST                    = 'kurJalaArRFtwhnZCoMxB2kKU'; // コグニートに既に設定されていたKEY
    const API_SECRET_TEST                 = 'oOCDmf29DyJyfxOPAaj8tSASzSPAHNepvbxcfVLkA9dJw7inYa';
    const CALLBACK_URL_TEST               = 'http://127.0.0.1:3000/#/reg/name';
    const CALLBACK_URL_PRODUCTION         = 'gocci.me/#/reg/name';
    const CALLBACK_HOME_URL_TEST          = 'http://192.168.1.93:3000/#/';
    const CALLBACK_HOME_URL_PRODUCTION    = 'gocci.me/#/reg/name';

    public static function setToken($token)
    {
      error_log('tokenがsetされました');
      // $this->token = $token;
      self::$token = $token;
    }

    public static function setImage($image)
    {
      error_log('imageがsetされました');
      // $this->image = $image;
      self::$image = $image;
    }

    public static function getToken()
    {
      error_log('getTokenが呼ばれました');
      return self::$token;
    }

    public static function getImage()
    {
      error_log('getImageが呼ばれました');
      return self::$image;
    }

    public function before()
    {
        self::session_check();
        // $this->session_check();
        // $this->http_x_request_check();
        self::http_x_request_check();
        self::accessLog();
    }

    public function start_basic()
    {
        switch (true) {
            case !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']):
            case $_SERVER['PHP_AUTH_USER'] !== 'gocci_web':
            case $_SERVER['PHP_AUTH_PW']   !== 'gocci_web':
                header('WWW-Authenticate: Basic realm="Enter username and password."');
                die('ログインが必要です');
        }
    }

    public static function http_x_request_check()
    {
        // SCRIPT要素で埋め込まれないための対策
        if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            $_SERVER['HTTP_X_REQUEST_WITH'] !== 'XMLHttpRequest') {
            // Not Ajax Request
            // json output
        }
    }

    public static function session_check()
    {
        if (session::get('user_id')) {
            error_log('sessionがあったので、user_idを更新/取得');
            self::set_user_id();
            $user_id = self::get_user_id();
            error_log($user_id);
        } else {
            error_log('sessionは存在しませんでした.jwtを取得します');
            $jwt = self::get_jwt();
            if ($jwt === "null" || is_null($jwt)) {
                error_log('jwtがnullでした');
                // self::unauth();
            }
            // $this->obj = self::getJwtObject($jwt);
            self::$obj = self::getJwtObject($jwt);
        }
    }

    // public static function get_user_id()
    public static function get_user_id()
    {
        // return $this->user_id;
        return self::$user_id;
    }

    public static function get_jwt_obj()
    {
        return $this->jwt_obj;
    }

    // public static function set_user_id()
    public static function set_user_id()
    {
        self::$user_id = session::get('user_id');
    }

    public function get_input_data() {

        $input_data = array_merge(Input::get(), Input::post());

        try {
            if (!empty($input_data)) {

            }
        } catch (ErrorException $e) {
            error_log($e);
            die('Error');
        }
    }

    /**
     * API BASE_DATA TEMPLATE
     *
     * @param  string $api_code
     * @param  string $api_message
     * @param  string $api_data
     * @param  string $jwt
     * @return Array  $base_data
     */
    public static function base_template($api_code, $api_message, $login_flag, $api_data, $jwt)
    {
        $base_data  = [
            "api_version" => self::API_VERSION,
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
     * ACCESS LOG
     */
    public static function accessLog()
    {
        if (self::ENV_DEV === 'DEVELOPMENT') {
            $accessTime = date('Y-m-d H:i:s', strtotime("+ 9 hour"));
            $ip = $_SERVER["REMOTE_ADDR"];
            error_log('Access Time: ');
            error_log($accessTime);
            error_log('Access IP:' . $ip);
        }
    }

    /**
     * JSON_ENCODE TEMPLATE
     * @param  Array  $status
     * @return Object $status
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
     * DEBUG_JSON_ENCODE_TEMPLATE
     * @param  Array  $status
     * @return Object $status
     */
    public static function debug_json_encode_template($status)
    {
        $status = json_encode(
            $status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
        return $status;
    }

    /**
     * POST CHECK
     */
    public static function post_check()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        } else {
            self::unauth();
        }
    }

    /**
     * @param String $uri
     * @param String $login_flag
     */
    public static function get_jwt_token($uri="", $login_flag)
    {
        $jwt = self::get_jwt();
        if(isset($jwt)) {
            self::setJwt($jwt);
        } else {
            self::unauth();
            error_log('UnAuthorized Accsess..');
            exit;
        }
     }

    /**
     * SET JWT
     * @param String $jwt
     */
    public static function setJwt($jwt)
    {
        $obj = self::runDeocd($jwt);
        if (empty($obj)) {
            self::unauth();
        }
        $user_id   = $obj->{'user_id'};
        session::set('user_id', $user_id);
        $username  = $obj->{'username'};
        session::set('username', $username);
        $exp       = $obj->{'exp'};
        session::set('exp', $exp);
    }

    /**
     * @return Object $obj
     */
    public static function runDeocd($jwt)
    {
        $data      = self::decode($jwt);
        $user_data = session::get('data');
        $obj       = json_decode($user_data);

        return $obj;
    }

    /**
     * DECODE
     * @param  String $jwt
     * @return Object $decoded
     */
    public static function decode($jwt)
    {
        $key = 'i_am_a_secret_key';
        try {
          error_log('decode Methodに渡された引数: ');
          error_log($jwt);
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $decoded = session::set('data', $decoded);
            error_log('decodedの中身を確認 by base decode');
        } catch (Exception $e){
            error_log($e);
            $decoded = "";
        }
        return $decoded;
    }

    /**
     * ENCODE (JWT CREATE)
     * @param  Int    $user_id
     * @param  String $username
     * @return string $jwt
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
     * CHECK IF THE JWT IS VALID
     * @param  String $exp
     * @return String $jwt
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
     * REFRESH TOKEN METHOD
     * @return String $jwt
     */
    public static function _refresh_token()
    {
        $user_id  = session::get('user_id');
        $username = session::get('username');

        Session::delete('exp');
        $jwt = self::encode($user_id, $username);
        error_log('-*-*-*-*JWT was update!-*-*-*');
        return $jwt;
    }

    /**
     * NOT JWT UNAUTH
     * @param String $uri
     * @param String $login_flag
     */
    public static function unauth($uri="",$login_flag=0)
    {
        error_log('アクセス拒否 base unauth method.');
        error_log('jwtが存在しません');
        $status = [
            "api_version" => self::API_VERSION,
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
     * @param $base_data
     *
     */
    public static function assignment_json($base_data)
    {
        return $json = self::json_encode_template($base_data);
    }

    /**
     * OUTPUT JSON
     * @param  Object $data
     */
    public static function output_json($api_data)
    {
        $json = self::json_encode_template($api_data);
        echo $json;
    }

    /**
     * DEBUG OUTPUT JSON
     * @param Object $data
     */
    public static function debug_output_json($api_data)
    {
        $json = self::debug_json_encode_template($api_data);
        echo $json;
    }

    /**
     * GET ALL HEADERS
     * @return Array $headers
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
     * NOT FOUND USER
     */
    public static function notfounduser()
    {
        $status = [
          'message' => 'NOTFOUND'
        ];
        $status = self::json_encode_template($status);
        echo $status;
        exit;
    }

    /**
     * NOT FOUND PAGE
     */
    public static function NotFoundPage()
    {
        $data = [
          "message" => "NOTFOUND"
        ];
        $base_data = self::base_template($api_code = "SUCCESS", 
          $api_message = "SUCCESS ful API request", 
          $login_flag =  1, 
          $data, $jwt = ""
        );
        $status = self::output_json($base_data);
    }

    /**
     * NOT ID
     */
    public static function notid()
    {
        $api_data = [
          'message' => 'NOTFOUND'
        ];
        $base_data = self::base_template($api_code = 1,
            $api_message = "SUCCESS", 
            $login_flag = 0, 
            $api_data, 
            $jwt = ""
        );
        $status    = self::json_encode_template($base_data);
        echo $status;
        exit;
    }

     /**
      * SUCCESS JSON OUTPUT
      * @return Object string
      */
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
              "api_version" => self::API_VERSION,
              "api_uri"     => Uri::string(),
              "api_code"    => "SUCCESS",
              "api_message" => "$message Successful API request",
              "api_data"    => $api_data
          ];
          $status = self::json_encode_template($status);
          echo $status;
          exit;
    }

    /**
     * ERROR SIGNIN
     */
    public static function error_signin($message)
    {
        $status = [
            "api_version" => self::API_VERSION,
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

    /**
     * ERROR JSON REGISTER
     */
    public static function error_register($message)
    {
        $status = [
            "api_version" => self::API_VERSION,
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

    /**
     * ERROR JSON OUTPUT
     * @return Object $status
     */
    public static function error_json($message)
    {
        $api_message = "UnAuthorized";

        $status = [
            "api_version" => self::API_VERSION,
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

    /**
     * TIMELIEN TEMPLATE
     * @return Array $data
     */
    public static function timeline_template()
    {
        $user_id  = session::get('user_id');
        $username = session::get('username');
        $exp      = session::get('exp');
        $jwt      = self::check_jwtExp($exp);
        $sort_key = 'all';
        $limit    = 18;
        $option   = [
            'call'        => Input::get('call', 0),
            'order_id'    => Input::get('order_id', 0),
            'category_id' => Input::get('category_id', 0),
            'value_id'    => Input::get('value_id', 0),
            'lon'         => Input::get('lon', 0),
            'lat'         => Input::get('lat', 0)
        ];
        $data = Model_Post::get_data($user_id, $sort_key, 0, $option, $limit);

        for ($i = 0; $i<$limit; $i++) {
            $post_id      = $data[$i]['post_id'];
            $post_user_id = $data[$i]['user_id'];
            $Comment_data = Model_Comment::get_data($post_id);
            $hash_id      = Hash_Id::video_hash($post_id);
            $user_hash_id = Hash_Id::create_user_hash($post_user_id);
            $data[$i]['hash_id']  = $hash_id;
            $data[$i]['user_hash_id'] = $user_hash_id;
            $data[$i] = [
                "post"     => $data[$i],
                "comments" => $Comment_data
            ];
        }
        return $data;
    }

    /**
     * USER TEMPLATE
     * @param  String $target_username
     * @param  Int    $limit
     * @param  String $sort_key
     * @return Array  $data
     */
    // public static function user_template($target_username, $limit, $sort_key) {
    public static function user_template($target_userhash, $limit, $sort_key) {
      if (ctype_digit($target_userhash)) { self::notid(); }

        $target_user_id = Hash_Id::get_user_hash($target_userhash);
        $user_id        = session::get('user_id');

        $user_id        = Controller_V1_Check::check_user_id_exists($target_user_id);
        $user_data      = Model_User::get_data($user_id, $target_user_id);
        $post_data      = Model_Post::get_data(
            $target_user_id, $sort_key, $target_user_id, $limit
        );
        for ($i = 0; $i<count($post_data); $i++) {
            $post_id = $post_data[$i]['post_id'];
            $Comment_data  = Model_Comment::get_data($post_id);
            $post_data[$i] = [
                "post"     => $post_data[$i],
                "comments" => $Comment_data
            ];
        }
        $data = [
            "header" => $user_data,
            "posts"  => $post_data
        ];

        return $data;
    }

    /**
     * REST TEMPLATE
     * @param  Int   $user_id
     * @param  Int   $rest_id
     * @return Array $data
     */
    public static function rest_template($user_id, $rest_id, $sort_key) {
        $rest_data= Model_Restaurant::get_data($user_id, $rest_id);
        $rest_data['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        $rest_data['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);
        $post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

        $loop_num = count($post_data);

        for ($i = 0; $i<$loop_num; $i++) {
            $post_id = $post_data[$i]['post_id'];
            $Comment_data = Model_Comment::get_data($post_id);

            $post_data[$i] = [
                "post"     => $post_data[$i],
                "comments" => $Comment_data
            ];
        }
        if (empty($user_id)) {
            $login_flag = 0;
        } else {
            $login_flag = 1;
        }
        $data = [
            "header" => $rest_data,
            "posts"  => $post_data
        ];
        return $data;
    }

    /**
     * VIDEO TEMPLATE
     * @param  Int    $user_id
     * @param  String $hash_id
     * @return Array  $data
     */
    public static function video_template($user_id, $hash_id)
    {
        $sort_key= "all";
        $post_id = Model_Post::get_post_id($hash_id);

        $data = Model_Post::get_one_data($user_id, $limit=1, $post_id);
        for ($i = 0; $i<$limit; $i++) {
            $Comment_data = Model_Comment::get_data($post_id);
            $data[$i]['hash_id']  = $hash_id;
                $data[$i] = [
                    "post"     => $data[$i],
                    "comments" => $Comment_data
                ];
        }
        return $data;
    }

    /**
     * GET JWT METHOD
     * @return String $jwt
     */
    public static function get_jwt()
    {
        $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";

        return $jwt;
    }

    /**
     * GET JWT OBJECT
     * @return Object $obj
     */
    public static function getJwtObject($jwt)
    {
        error_log('jwtがあるかチェックします');
        error_log($jwt);
        if (empty($jwt)) {
            error_log('jwtが存在しないためencodeします');
            // self::unauth(Uri::string(), $login_flag=0);
            // exit;
        }
        $data      = self::decode($jwt);
        $user_data = session::get('data');
        $obj       = json_decode($user_data);
        return $obj;
    }

    /**
     * EXPIRED TOKEN
     * @param  String $message
     */
    public static function expired_token($message)
    {
          $status = [
            "api_version" => self::API_VERSION,
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

    // Request Token
    public static function getRequestToken()
    {
        $API_KEY_TEST    = self::API_KEY_TEST;
        $API_SECRET_TEST = self::API_SECRET_TEST;
        $callback_url = ( !isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;
        // リクエストトークンの取得
        $access_token_secret = '' ;
        // エンドポイントURL
        $request_url = 'https://api.twitter.com/oauth/request_token';
        // リクエストメソッド
        $request_method = 'POST' ;
        // キーを作成する (URLエンコードする)
        $signature_key = rawurlencode( $API_SECRET_TEST ) . '&' . rawurlencode( $access_token_secret ) ;
        // パラメータ([oauth_signature]を除く)を連想配列で指定
        $params = [
            'oauth_callback'         => $callback_url,
            'oauth_consumer_key'     => $API_KEY_TEST,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => microtime(),
            'oauth_version'          => '1.0',
        ];
        // 各パラメータをURLエンコード
        foreach ($params as $key => $value) {
            if ($key == 'oauth_callback') {
                continue;
            }
            // URLエンコード処理
            $params[$key] = rawurlencode($value);
        }
        // 連想配列をアルファベット順に並び替える
        ksort($params);

        // パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
        $request_params = http_build_query( $params , '' , '&' ) ;
        // 変換した文字列をURLエンコードする
        $request_params = rawurlencode( $request_params ) ;
        // リクエストメソッドをURLエンコードする
        $encoded_request_method = rawurlencode( $request_method ) ;
        // リクエストURLをURLエンコードする
        $encoded_request_url = rawurlencode( $request_url ) ;
        // リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
        $signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;
        // キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
        $hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;
        // base64エンコードして、署名[$signature]が完成する
        $signature = base64_encode( $hash ) ;
        // パラメータの連想配列、[$params]に、作成した署名を加える
        $params['oauth_signature'] = $signature ;
        // パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
        $header_params = http_build_query( $params , '' , ',' ) ;
        // リクエスト用のコンテキストを作成する
        $content = [
            'http' => [
                'method' => $request_method,
                'header' => [
                    'Authorization: OAuth ' . $header_params,
                ],
            ],
        ];
        // cURLを使ってリクエスト
        $curl = curl_init() ;
        curl_setopt( $curl , CURLOPT_URL , $request_url ) ;
        curl_setopt( $curl , CURLOPT_HEADER, 1 ) ; 
        curl_setopt( $curl , CURLOPT_CUSTOMREQUEST , $content['http']['method'] ); // メソッド
        curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false );                     // 証明書の検証を行わない
        curl_setopt( $curl , CURLOPT_RETURNTRANSFER , true );                      // curl_execの結果を文字列で返す
        curl_setopt( $curl , CURLOPT_HTTPHEADER , $content['http']['header'] );    // ヘッダー
        curl_setopt( $curl , CURLOPT_TIMEOUT , 5 );                                // タイムアウトの秒数
        $res1 = curl_exec( $curl );
        $res2 = curl_getinfo( $curl );
        curl_close( $curl );

        // 取得したデータ
        $response = substr( $res1, $res2['header_size'] );
        $header   = substr( $res1, 0, $res2['header_size'] );

        // リクエストが成功しなかった場合
        if ( !isset($response) || empty($response)) {
            $error = 'リクエストが失敗してしまったようです。Twitterかの応答自体ありません';
        } else {
            // 成功した場合
            // 文字列を[&]で区切る
            $parameters = explode( '&' , $response ) ;
            if( !isset( $parameters[1] ) || empty( $parameters[1] ) ) {
                $error_msg = true ;
            } else {
                // それぞれの値を格納する配列
                $query = array() ;
                // [$parameters]をループ処理
                foreach( $parameters as $parameter )
                {
                    // 文字列を[=]で区切る
                    $pair = explode( '=' , $parameter ) ;

                    // 配列に格納する
                    if( isset($pair[1]) ) {
                      $query[ $pair[0] ] = $pair[1] ;
                    }
                }
                // エラー判定
                if( !isset( $query['oauth_token'] ) || !isset( $query['oauth_token_secret'] ) ) {
                    $error_msg = true ;
                } else {
                    return $query['oauth_token'] .";" . $query['oauth_token_secret'];
                    exit;
                }
            }
            // エラーの場合
            if( isset( $error_msg ) && !empty( $error_msg ) ) {
                $error = '' ;
                $error .= 'リクエストトークンを取得できませんでした。[$API_KEY_TEST]と[$callback_url]、そしてTwitterのアプリケーションに設定している[Callback URL]を確認して下さい。' ;
                $error .= '([Callback URLに設定されているURL]→<mark>' . $callback_url . '</mark>)' ;
                error_log($error);
            }
        }
        // エラーメッセージがある場合
        if ( isset($error) && $error) {
            error_log($error);
            exit;
        }
        return $query['oauth_token'];
    }

    public static function get_twitter_data()
    {
        $API_KEY_TEST    = self::API_KEY_TEST;
        $API_SECRET_TEST = self::API_SECRET_TEST;
        $tof = "";

        // Callback URL
        $Callback_url = ( !isset($_SERVER['HTTPS']) ||
        empty($_SERVER['HTTPS']) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // 連携アプリを認証をクリックして帰ってきた時
        if (isset( $_GET['oauth_token'] ) && !empty( $_GET['oauth_token'] ) ) {
            session_start();
            @$request_token_secret = $_SESSION['oauth_token_secret'];
            $request_url = self::REQUEST_URL;
            $request_method = 'POST';
            $signature_key = rawurlencode($API_SECRET_TEST) . '&' . rawurlencode($request_token_secret);

            $params = [
                'oauth_consumer_key'    => $API_KEY_TEST,
                'oauth_token'           => $_GET['oauth_token'],
                'oauth_signature_method'=> 'HMAC-SHA1',
                'oauth_timestamp'       => time(),
                'oauth_verifier'        => @$_GET['oauth_verifier'],
                'oauth_nonce'           => microtime(),
                'oauth_version'         => '1.0',
            ];

            foreach ($params as $key => $value)
            {
                $params[$key] = rawurldecode($value);
            }
            // 連想配列をアルファベット順に並び替え
            ksort($params);
            // パラメータの連想配列を[キー=値&キー=値...]の文字列に変換
            $request_params = http_build_query($params, '', '&');
            // 変更した文字列をURLエンコードする
            $request_params = rawurlencode($request_params);
            // リクエストUメソッドをURLエンコードする
            $encoded_request_method = rawurlencode($request_method);
            // リクエストURLをURLエンコードする
            $signature_data = $encoded_request_method . '&' . $encoded_request_method . '&' . $request_params;
            // キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
            $hash = hash_hmac('sha1', $signature_data, $signature_key, TRUE);
            // base64エンコードして、著名[$signature]が完成
            $signature = base64_encode($hash);
            // パラメータの連想配列、[$params]に、作成した著名を加える
            $params['oauth_signature'] = $signature;
            // パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
            $header_params = http_build_query($params, '', ',');
            // リクエスト用のコンテキストを作成する
            $context = [
                'http' => [
                    'method' => $request_method,
                    'header' => [
                        'Authorization: OAuth ' . $header_params,
                    ],
                ],
            ];
            // cURLでリクエスト
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $request_url);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context['http']['method']);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $context['http']['header']);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            $res1 = curl_exec($curl);
            $res2 = curl_getinfo($curl);
            curl_close($curl);
            $response = substr($res1, $res2['header_size']);
            $header   = substr($res1, 0, $res2['header_size']);

            if (!isset($response) || empty($response)) {
                $error = 'リクエストが失敗しました。Twitterの応答自体ありません';
                $data = [
                    "error_msg" => $error,
                ];
                $base_data = self::base_template($api_code = "SUCCESS",
                    $api_message = "Successful API request",
                    $login_flag  = 1, $data, $jwt = ""
                );
                echo self::output_json($base_data);
            } else {
                error_log('文字列を区切ります');
                // 文字列を[&]で区切る
                $parameters = explode('&', $response);
                // エラー判定
                if ( !isset($parameters[1] ) || empty( $parameters[1])) {
                    error_log('errror1');
                    $error_msg = true;
                } else {
                    // それぞれの値を格納する配列
                    $query = [];
                    // [$parameters]をループ処理
                    foreach ($parameters as $parameter)
                    {
                        // 文字列を[=]で区切る
                        $pair = explode('=', $parameter);
                        // 配列に格納
                        if (isset($pair[1])) {
                            $query[$pair[0]] = $pair[1];
                        }
                    }
                    // if ( isset($query['oauth_token']) || !isset($query['oauth_token_secret'])) {
                    if (!isset($query['oauth_token']) ) {
                        error_log('error2');
                        $error_msg = true;
                    } else {
                        // register
                        error_log('errorなし！それぞれの値を変数に代入します');
                        $oauth_token = $query['oauth_token'];
                        $oauth_token_secret = $query['oauth_token_secret'];
                        $token = $oauth_token . ";" . $oauth_token_secret;
                        // $user_id = $query['user_id'];
                        $screen_name = $query['screen_name'];
                        $image = "http://www.paper-glasses.com/api/twipi/" . $screen_name;
                        $tof = true;

                        $user_id = Model_User::get_next_id();
                        session::set('user_id', $user_id);

                        error_log('checkするtoken');
                        error_log($token);
                        $judge = Model_Token::check_tokne($token); // $そいつのuser_idを引数に渡す
                        error_log($judge);
                        if ($judge === true) {
                          // insert
                          Model_Token::insert_token($user_id, $token, $image);
                          error_log('insert done');
                        } else {
                          // すでに登録してある。
                          error_log('既にそのtwitterアカウントは登録されていましたので、処理を終了します');
                          $token = $judge;
                          error_log($token);
                          error_log('reg/nameへリダイレクトします!!');
                          header('Location: http://test.web.api.gocci.me/v1/auth/twitter_sign_in/?token=' .$token);
                          // header('Location: https://web.api.gocci.me/v1/auth/twitter_sign_in/?token=' .$token);
                          exit;
                        }
                    }
                }
            }
        } else if( isset($_GET['denied']) && !empty( $_GET['denied'])) {
            // キャンセルクリックして返ってきた時、エラーメッセージを出力して終了
          error_log('キャンセルクリックが押されました');
            header('Location: ' . self::CALLBACK_HOME_URL_TEST);
            exit;
        } else {
            // 認証クリックしていない時
            $tof = false;
            error_log('認証クリックしていない時');
        }
        if ( empty($data)) {
            $data = [];
        } else {
            $access_token = $data['oauth_token'];
        }
        if ($tof) {
          // twitter認証ボタンクリック完了後
          error_log('reg/nameへリダイレクトします');
          error_log('register 1回目のリダイレクト');
          header('Location: ' . self::CALLBACK_URL_TEST); // test /reg/nameへ。(register)
          // header('Location: ' . self::CALLBACK_URL_PRODUCTION); // production
          exit;
        } else {
          error_log('tofがtrueではないのでリダイレクトしない');
        }
    }
}