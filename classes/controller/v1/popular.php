<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Pupular api
 *
 */

class Controller_V1_Popular extends Controller
{

    public function action_index()
    {

        $user_id = Input::get('user_id');
        $limit 	 = Input::get('limit');

		if (empty($limit)) {
		    $limit = 20;
		}


		//--------------------------------------------//
		//"POST_Data"

		$limit = 10;
		$sort_key  = 'post';

		$post_id = Model_Like::get_rank($limit);

		for ($i=0; $i < $limit; $i++) {
			$post_data[$i] = Model_Post::get_data($user_id, $sort_key, $post_id[$i]['like_post_id'], $limit);
		}

		$data = array("posts" => $post_data);

	   	$popularpage = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	   	echo "$popularpage";

	}
}