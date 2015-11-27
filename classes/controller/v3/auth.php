<?php
/**
 * Auth Class Api
 * @package    Gocci-Web
 * @version    3.0 <2015/11/26>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V3_Auth extends Controller_Rest
{
    use Auth;

    /**
     * format json
     */
    protected $format = "json";
    /**
     * @var String $keyword
     */
    private static $keyword = "";

    /**
     * @var String $provider
     */
    private static $provider = "";

    /**
     * @var String token
     */
    private static $token = "";

    /**
     * sns login api
     */
    public function post_sns_login()
    {
        $provider = Input::get('provider');
        $token    = Input::get('token');

        Auth::sns_login($provider, $token);
    }

    /**
     * pass login
     */
    public function post_pass_login()
    {
        $username = Input::get('username');
        $password = Input::get('password');

        Auth::pass_login($username, $password);
    }

    /**
     * logout
     */
    public function post_logout()
    {
        Auth::logout();
    }

}