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
		$user_data = Input::get();
		//print_r($user_data);

		Model_Validation::check_signup($user_data);
	}
}
