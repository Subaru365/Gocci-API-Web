<?php
/**
 * Statuses API
 * @package    Gocci-Web
 * @version    3.0 <2015/11/24>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V3_Statuses extends Controller_Rest
{
    use GocciAPI;

    public function get_timeline()
    {
        $jwt = GocciAPI::get_jwt();
        $obj = GocciAPI::getJwtObject($jwt);
        GocciAPI::get_timeline_result($obj, $jwt);
    }

    public function get_rest()
    {

    }

    public function get_notice_badge()
    {
      
    }
}

