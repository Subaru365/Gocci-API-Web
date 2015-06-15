<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * notice api
 *
 */

class Controller_V1_Notice extends Controller
{

    public function action_index()
    {
    	$user_id = Input::get('user_id');

    	$data = Model_Notice::get_data($user_id);

    	$notice = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

	   	echo "$notice";

	   	$result = Model_Notice::reset($user_id);
	}

}