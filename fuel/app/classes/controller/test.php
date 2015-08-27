<?php

use Aws\Sns\SnsClient;

/**
*
*/
class Controller_Test extends Controller
{

	//Followline
	public function action_index()
	{
		$user_id = Input::get('user_id');

		$num = count($user_id);

		print_r($user_id);

	}

}
