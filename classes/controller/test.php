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
		$sort_key  = 'follow';
        $user_id   = Input::get('user_id');

		$follow_id = Model_Follow::get_follow_id($user_id);
		$data = Model_Post::get_data($user_id, $sort_key, $follow_id);

	   	$status = $this->output_json($data);
	}

}
