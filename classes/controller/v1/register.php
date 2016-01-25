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

    public static function getToken()
    {
        return parent::getToken();
    }

    public static function getImage()
    {
        return parent::getImage();
    }

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
        $provider = "api.twitter.com";
        $username = Input::post('username');
        // $sns_token= Input::post('token');
        error_log($username);

        session_start();
        error_log('いまここ1');
        error_log('user_idを取得します');
        error_log($user_id);
        $data = Model_Token::get_token_data($user_id);
        if (empty($data)) {
            error_log('tokenが空です');
            Controller_V1_Base::error_json('empty token.');
            exit;
        }
        $sns_token = $data[0]['token'];
        $image     = $data[0]['image'];
        $this->post_check();

        if (empty($image) || empty($sns_token)) {
            error_log('img/tokenどちらも空のため終了します');
            exit;
        }
        error_log('いまここ2');
        self::register_user($username, $user_id, $provider, $sns_token, $image, $badge_num);
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
            // usernameが既に使われていないかエラーハンドリング
            $username = Model_User::check_web_name($username);
            $username = Model_User::empty_name($username);
            $username = Model_User::format_name_check($username);

            // facebook/twitterアカウントデータをusers
            error_log('Cognitoにuser情報を追加してidentityidを発行しSNS連携します');
            $cognito_data = Model_Cognito::post_web_sns($user_id, $provider, $sns_token);
            error_log('identityidを発行しましt');
            $identity_id  = $cognito_data['IdentityId'];

            // users table insert
            $profile_img  = Model_User::sns_insert_data($username, $identity_id, $profile_img);
            $endpoint_arn = 0;

            // 連携したので、flagを更新
            Model_User::update_sns_flag($user_id, $provider);
            // jwt 生成
            try {
                $jwt = self::encode($user_id, $username);
                error_log('jwt:  ');
                error_log($jwt);
            } catch (Exception $e) {
                error_log($e);
                $jwt = "";
            }
            $user_hash_id = Hash_Id::create_user_hash($user_id);

            $data = [
                "user_id"     => $user_id,
                "user_hash_id"=> $user_hash_id,
                "username"    => $username,
                "identity_id" => $identity_id,
                "profile_img" => $profile_img,
                "badge_num"   => $badge_num
            ];
            error_log('jsonを出力します');
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag  = 1, $data, $jwt
            );
            $status = self::output_json($base_data);
            error_log('json出力しました');
        } catch (\Database_Exception $e) {
            error_log('sns sign_up Error: ');
            error_log($e);
            exit;
        }
    }
}