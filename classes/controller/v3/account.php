<?php
/**
 * Account API
 * ユーザ登録や、ユーザ表示などユーザアカウントに関するAPIです
 *
 * @package    Gocci-Web
 * @version    3.0 <2015/11/24>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V3_Account extends Controller_Rest
{
    use Register;

    /**
     * @var String $os
     */
    private $os = "";

    /**
     * @var String $model
     */
    private $model = "";

    /**
     * @var Int $register_id
     */
    private $register_id = "";

    /**
     * @var Int $badge_num
     */
    private $badge_num = "";

    /**
     * @var String $keyword
     */
    private $keyword = "";


    /**
     * User page api
     * @param String $target_username
     */
    public function get_user($target_username = "none")
    {
        $jwt = GocciAPI::get_jwt();
        GocciAPI::NotJwtUser($jwt, $target_username);
    }

    public function action_sign_up()
    {
        $user_id  = Model_V2_DB_User::getNextId();
        $username = Input::post('username');
        $password = Input::post('password');
        $register_id = $user_id;

        Register::sign_up($username, $password,
          $user_id,
          $register_id,
          $os = $this->os = "web",
          $model = $this->model = "PC",
          $badge_num = $this->badge_num = 0);
    }

    public function action_sns_sign_up()
    {
        $user_id     = Model_V2_DB_User::getNextId();
        $username    = Input::post('username');
        $profile_img = Input::post('profile_img');
        $sns_token   = Input::post('token');
        $provider    = Input::post('provider');
        $register_id = $user_id;

        Register::sns_sign_up($username, $user_id,
            $provider, $sns_token, $profile_img,
            $os = $this->os = "web",
            $model = $this->model = "PC",
            $register_id,
            $badge_num = $this->badge_num = 0);

    }

}