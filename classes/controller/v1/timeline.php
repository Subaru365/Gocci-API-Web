<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * timeline api
 *
 */

class Controller_V1_Timeline extends Controller
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

		$sort_key  = 'all';
		$post_data = Model_Post::get_data($user_id, $sort_key, $sort_key, $limit);


	   	$timelinepage = json_encode($post_data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	   	echo "$timelinepage";

	}
}

