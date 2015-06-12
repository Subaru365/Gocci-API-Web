<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * restpage api
 *
 */

class Controller_V1_Rest extends Controller
{

    public function action_index()
    {
    	$rest_id = Input::get('rest_id');
        $user_id = Input::get('user_id');
		$limit   = Input::get('limit');

		if (empty($limit)) {
		    $limit = 30;
		}


		if (!empty($rest_id)) {

			//--------------------------------------------//
			//"Rest_Data"
			//--------------------------------------------//

			$rest_data = Model_Restaurant::get_data($rest_id);


			//後に実装
			//$cheer_num = Model_Cheer::get_flag($rest_id);
			//$rest_data['0']['rest_cheer_num'] = $cheer_num;


			$want_flag = Model_Want::get_flag($user_id, $rest_id);
			$rest_data['0']['want_flag']= $want_flag;



			//--------------------------------------------//
			//"POST_Data"
			//--------------------------------------------//

			$sort_key  = 'rest';
			$post_data = Model_Post::get_data($sort_key, $rest_id, $limit);
			$post_num  = count($post_data);


			for ($i=0; $i < $post_num; $i++) {

				$post_id	  = $post_data[$i]['post_id'];
				$post_user_id = $post_data[$i]['post_user_id'];
				$post_rest_id = $post_data[$i]['post_rest_id'];


	    		$like_num 	  = Model_Like::get_num($post_id);
	    		$post_data[$i]['like_num']    = $like_num;

	    		$comment_num  = Model_Comment::get_num($post_id);
	    		$post_data[$i]['comment_num'] = $comment_num;

	    		$want_flag	  = Model_Want::get_flag($user_id, $post_rest_id);
	    		$post_data[$i]['want_flag']	  = $want_flag;

	    		$follow_flag  = Model_Follow::get_flag($user_id, $post_user_id);
	    		$post_data[$i]['follow_flag'] = $follow_flag;

	    		$like_flag	  = Model_Like::get_flag($user_id, $post_id);
	    		$post_data[$i]['like_flag']   = $like_flag;

			}


	    	$data = array(
	    		"restaurants" => $rest_data[0],
	    		"posts" => $post_data
	    	);

	    	$restaurantpage = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	    	echo "$restaurantpage";

	    	//echo "$_SERVER['REQUEST_TIME']"

	    }
	}
}
