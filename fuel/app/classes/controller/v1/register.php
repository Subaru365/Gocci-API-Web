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

            $data = [
                "user_id"     => $user_id,
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
     * sns register
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

        // provider facebookの時
        /*
        if ($provider === 'graph.facebook.com)') {
            $username    = Input::post('username');
            $profile_img = Input::post('profile_img');
            $sns_token   = Input::post('token');
            $provider    = Input::post('provider');
        } else {
            $username    = Input::post('username');

            // サーバ側で保持していたtwitte_proifile_img / tokenを持ってくる
            // $profile_img = get_
            // $snn_token = get?
            // $provider = "api.twitter";
        }
        */

        $this->post_check();

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

            $data = [
                "user_id"     => $user_id,
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
}
