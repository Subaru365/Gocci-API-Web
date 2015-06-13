<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);

class Controller_V1_Test extends Controller
{

	public function action_index()
	{

	$rest_id = Input::get('rest_id');
    $user_id = Input::get('user_id');
	$limit   = Input::get('limit');

		$sort_key  = 'rest';
		$post_data = Model_Post::get_data($user_id, $sort_key, $rest_id, $limit);

		$data = array(
	    		"posts" => $post_data
	    	);

		$restaurantpage = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	    echo "$restaurantpage";



	}
}