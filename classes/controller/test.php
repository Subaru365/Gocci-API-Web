<?php

use Aws\Sns\SnsClient;

/**
*
*/
class Controller_Test extends Controller
{
	public static function action_index()
	{
		$identity_id 	= Input::get('identity_id');
		$user_data		= Model_User::get_auth($identity_id);
		$user_id 		= $user_data['user_id'];
		Model_Sns::post_message('Test', 1, $user_id);
	}
}
