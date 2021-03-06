<?php
/**
 * PUT api     既存のリソースのアップデート
 * @package    Gocci-Web
 * @version    3.0 <2015/12/22>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V1_Put extends Controller_V1_Base
{
    /**
     * refresh token api
     * 現在のJWTが有効期限内であれば有効期限を延長する
     */
    public static function action_refresh_token()
    {
        error_log('refresh_token method');
        self::get_jwt_token($uri=Uri::string(), $login_flag =0);
        $user_id  = session::get('user_id');
        $username = session::get('usernaem');
        Model_User::check_user_IdName($user_id, $username);

        // 古いSessionデータexpを破棄する
        Session::delete('exp');
        $jwt = self::encode($user_id, $username);

        $data = [
            "message" => "jwtが更新されました"
        ];
        $base_data = self::base_template($api_code = "SUCCESS", 
            $api_message = "Sucessful API request",
            $login_flag = 1, $data, $jwt
        );
        $status = self::output_json($base_data);
   }
}
