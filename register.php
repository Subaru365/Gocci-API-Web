<?php
/**
 * Register Trait
 * @package    Gocci-Web
 * @version    3.0 <2015/11/26>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

trait Register
{
    use GocciAPI;
   /**
     * @param String $username
     * @param String $password
     * @param Int $register_id
     * @param Int $user_id
     */
    public static function sign_up($username,
        $password,
        $register_id,
        $user_id,
        $os,
        $model,
        $badge_num)
    {
        GocciAPI::post_check();

        try {
            // username,passwordが両方空か
            Model_V2_DB_User::checkNamePass($username, $password);

            // 既に使用されていないか
            $username = Model_V2_DB_User::checkName($username);

            // usernameは空ではないか
            $username = Model_V2_DB_User::formatNameCheck($username);

            Model_V2_DB_Device::checkRegisterId($register_id);

            $cognito_data = Model_V2_AWS_Cognito::postData($user_id);

            // コグニートID
            $identity_id = $cognito_data['identityId'];
            $token       = $cognito_data['Token'];

            // user登録
            $hash_pass   = password_hash($password, PASSWORD_BCRYPT);
            $profile_img = Model_V2_DB_User::insertData($username, $identity_id, $hash_pass);
            $endpoint_arn = 0;
            Model_V2_DB_Device::postData($user_id, $os, $model, $register_id, $endpoint_arn);

            $jwt = self::encode($user_id, $username);

            $data = [
              "user_id"     => $user_id,
              "username"    => $username,
              "profile_img" => $profile_img,
              "identity_id" => $identity_id,
              "badge_num"   => $badge_num
            ];
            $base_data = GocciAPI::base_template($api_code = "SUCCESS",
              $api_message = "Successful API request", 
              $login_flag = 1,
              $data, 
              $jwt);

            GocciAPI::output_json($base_data);

        } catch (\Database_Exception $e) {
            var_dump($e);
            exit;
        }
    }

    /**
     * sns register
     *
     * @param String POST $username
     * @param String POST $profile_img
     * @param String POST $token
     * @param String POST $provider
     */
    public static function sns_sign_up($username, $user_id,
      $provider, $sns_token, $profile_img,
      $os, $model, $register_id,
      $badge_num
    )
    {
        GocciAPI::post_check();

        try {
            // usernameが既に登録されていないかエラーハンドリング
            $username = Model_V2_DB_User::checkName($username);
            $username = Model_V2_DB_User::emptyName($username);
            $username = Model_V2_DB_User::formatNameCheck($username);

            // facebook/twitterアカウトデータをusers/devicesに保存
            $cognito_data = Model_V2_AWS_Cognito::postSns($user_id, $provider, $sns_token);
            $identity_id = $cognito_data['identity_id'];

            $profile_img = Model_V2_DB_User::snsInsertData($username, $identity_id, $profile_img);

            Model_V2_DB_Device::postData($user_id, $os, $model, $register_id, $endpoint_arn = 0);

            Model_V2_DB_User::updateSnsFlag($user_id, $provider);
            $jwt = GocciAPI::encode($user_id, $username);

            $data = [
              "user_id"     => $user_id,
              "username"    => $username,
              "identity_id" => $identity_id,
              "profile_img" => $profile_img,
              "badge_num"   => $badge_num
            ];
            $base_data = GocciAPI::base_template($api_code = "SUCCESS", $api_message = "Successful API request", 
              $login_flag = 1, $data, $jwt);

            GocciAPI::output_json($base_data);
            exit;
        } catch (\Database_Exception $e) {
            var_dump($e);
            exit;
        }
    }
}
