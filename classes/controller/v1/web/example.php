<?php
/**
 * Example Class 
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
 
class Controller_V1_Web_Example extends Controller_V1_Web_Base
{
    public static function action_uri()
    {
	echo Uri::string();
    }

    public static function action_test_json()
    {
        $api_code = 0;
	$api_message = "success";
	$api_data = [
	    "test" => "test_json"
	];
	$jwt = "Abw8Rsasfw_Ejgw";
	$base_data = self::base_template($api_code, $api_message, $api_data, $jwt);
	print_R($base_data);
    }
    
}


