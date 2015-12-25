<?php
/**
 * Register Class Api
 * @package    Gocci-Web
 * @version    3.0 <2015/12/22>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V1_Register extends Controller_V1_Base
{
    const REQUEST_URL      = 'https://api.twitter.com/oauth/access_token';
    const PROVIDER_TWITTER = 'api.twitter.com';
    const API_KEY_TEST     = 'kurJalaArRFtwhnZCoMxB2kKU'; // コグニートに既に設定されていたKEY
    const API_SECRET_TEST  = 'oOCDmf29DyJyfxOPAaj8tSASzSPAHNepvbxcfVLkA9dJw7inYa'; //
    // const API_KEY_TEST          = '3rrbNV3OXeBjKlZV3NRQRNS0k'; // 自前で用意したKEY
    // const API_SECRET_TEST       = 'LEblop9pEOemasvddlGuvMzpkKc6608TuIhTaxU4YtiCaE3VjE'; // 自前
    // const API_KEY_PRODUCTION = '';
    // const API_SECRET_PRODUCTION = '';

    /**
     * username password register
     * @param string POST $username
     * @param string POST $password
     */
    public function action_sign_up()
    {
        $keyword  = 'サインアップ';
        $badge_num= 0;
        $user_id  = Model_User::get_next_id();
        $username = Input::post('username');
        $password = Input::post('password');
        $register_id = $user_id; 

        $this->post_check();

        try {
            // usernameとpasswordが両方空か
            Model_User::check_name_pass($username, $password);

            // 既に使用されていないか
            $username = Model_User::check_web_name($username);

            // usernameは空ではないか
            $username = Model_User::empty_name($username);

            // usernameの文字数が制限以内か
            $username = Model_User::format_name_check($username);

            // passwordの文字数チェックする(最低6文字以上)
            $password = Model_User::format_password_check($password);

            // Model_Device::check_register_id($register_id);
            $cognito_data = Model_Cognito::post_data($user_id);

            // コグニートID
            $identity_id  = $cognito_data['IdentityId'];
            $token        = $cognito_data['Token'];
            // user登録
            $hash_pass    = password_hash($password, PASSWORD_BCRYPT);
            $profile_img  = Model_User::insert_data($username, $identity_id, $hash_pass);
            $endpoint_arn = 0;
            $jwt = self::encode($user_id, $username);
            $user_hash_id = Hash_Id::create_user_hash($user_id);
            $data = [
                "user_id"     => $user_id,
                "user_hash_id"=> $user_hash_id,
                "username"    => $username,
                "profile_img" => $profile_img,
                "identity_id" => $identity_id,
                "badge_num"   => $badge_num
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag  = 1, $data, $jwt
            );

            $status = $this->output_json($base_data);
        } catch(\Database_Exception $e) {
            error_log('Exception sign_up');
            error_log($e);
            exit;
        }
    }

    /**
     * sns(Facebook) register
     *
     * @param string POST $username
     * @param string POST $profile_img
     * @param string POST $token
     * @param string POST $provider
     */
    public function action_sns_sign_up()
    {
        $keyword     = "SNS登録";
        $badge_num   = 0;
        $user_id     = Model_User::get_next_id();
        $register_id = $user_id;
        $username    = Input::post('username');
        $profile_img = Input::post('profile_img');
        $sns_token   = Input::post('token');
        $provider    = Input::post('provider');

        self::register_user($username, $user_id, $provider, $sns_token, $profile_img, $badge_num);
    }

    /**
     * sns(twitter) register
     * @param String POST $username
     */
    public function action_twitter_sign_up()
    {
        $keyword  = "SNS登録";
        $user_id  = Model_User::get_next_id();
        $badge_num= 0;
        $provider = "api.twitter";
        $username = Input::post('username');

        session_start();
        /*
        $profile_img = Input::post('profile_img');
        $sns_token   = Input::post('token');
        */

        // サーバ側で保持していたtwitte_proifile_img / tokenを持ってくる
        if (isset($_SERVER['profile_img']) && isset($_SERVER['sns_token'])) {
            echo $profile_img = $_SERVER['profile_img'];
            echo $sns_token   = $_SERVER['sns_token'];
        } else {
            echo 'no session';
            $profile_img = '';
            $sns_token   = '';
        }
        $this->post_check();

        self::register_user($username, $user_id, $provider, $sns_token, $profile_img, $badge_num);
    }

    public static function register_user(
        $username,
        $user_id,
        $provider,
        $sns_token,
        $profile_img,
        $badge_num
    )
    {
        try {
            error_log('register action_sns_sign_up 叩きました in try');
            // usernameが既に使われていないかエラーハンドリング
            $username = Model_User::check_web_name($username);
            error_log('check ok name!');
            $username = Model_User::empty_name($username);
            error_log('check ok not empty name!');
            $username = Model_User::format_name_check($username);
            error_log('check ok name no prblem!');

            // facebook/twitterアカウントデータをusers
            $cognito_data = Model_Cognito::post_web_sns($user_id, $provider, $sns_token);
            $identity_id  = $cognito_data['IdentityId'];

            // users table insert
            $profile_img  = Model_User::sns_insert_data($username, $identity_id, $profile_img);
            $endpoint_arn = 0;

            // 連携したので、flagを更新
            Model_User::update_sns_flag($user_id, $provider);
            // jwt 生成
            $jwt = self::encode($user_id, $username);
            $user_hash_id = Hash_Id::create_user_hash($user_id);
            $data = [
                "user_id"     => $user_id,
                "user_hash_id"=> $user_hash_id,
                "username"    => $username,
                "identity_id" => $identity_id,
                "profile_img" => $profile_img,
                "badge_num"   => $badge_num
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag  = 1, $data, $jwt
            );
            $status = $this->output_json($base_data);
            exit;
        } catch (\Database_Exception $e) {
            error_log('sns sign_up Error: ');
            error_log($e);
            exit;
        }
    }

    /*
    private static function get_twitter_data()
    {
        $API_KEY_TEST    = self::API_KEY_TEST;
        $API_SECRET_TEST = self::API_SECRET_TEST;

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
                'oauth_verifier'        => $_GET['oauth_verifier'],
                'oauth_nonce'           => microtime(),
                'oauth_version'         => '1.0',
            ];

            foreach ($params as $key => $value)
            {
                $params[$Key] = rawurldecode($value);
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
            curl_setopt($curl, CURLOPT_HTTPHEADER, $context['http'['header']]);
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
                // 文字列を[&]で区切る
                $parameters = explode('&', $response);
                // エラー判定
                if ( !isset($parameters[1] ) || empty( $parameters[1])) {
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
                    if ( isset($query['oauth_token']) || !isset($query['oauth_token_secret'])) {
                        $error_msg = true;
                    } else {
                        $access_token = $query['oauth_token'];
                        $access_token_secret = $query['oauth_token_secret'];
                        $user_id = $query['user_id'];
                        $screen_name = $query['screen_name'];
                        $image = "http://www.paper-glasses.com/api/twipi/" . $screen_name;
                    }
                }
                // エラーの場合
                if ( isset($error_msg) && !empty($error_msg)) {
                    $error = '';
                    $error .= 'アクセストークンを取得できませんでした。セッションが上手く働いていない可能性があります';
                    $data = [
                        "error_msg" => $error_msg
                    ];
                    $base_data = self::base_template($api_code = "SUCCESS",
                            $api_message = "Successful API request",
                            $login_flag  = 1,
                            $data,
                            $jwt = ""
                    );
                    echo self::output_json($base_data);
                }
            }
            // セッション終了
            $_SESSION = [];
            session_destroy();
        } else if( isset($_GET['denied']) && !empty( $_GET['denied'])) {
            // キャンセルクリックして返ってきた時、エラーメッセージを出力して終了
            die('You have rejected the app');
            exit;
        } else {
            $oauth_token = self::getRequestToken();
            return $oauth_token;
        }
        if ( isset ($error) && $error) {
            die($error);
        }
        if ( empty($data)) {
            $data = [];
        } else {
            $access_token = $data['oauth_token'];
        }
        $token = $access_token . ";" . $query['oauth_token_secret'];
        $data[0]['access_token']        = $access_token;
        $data[0]['access_token_secret'] = $query['oauth_token_secret'];
        $data[0]['token']               = $token;
        $data[0]['image']               = $image;
        return $data;
    }
    */
}
