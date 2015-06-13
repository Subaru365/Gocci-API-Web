<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * commentpage api
 *
 */

class Controller_V1_Comment extends Controller
{

    public function action_index()
    {

    	$user_id = Input::get('user_id');
        $post_id = Input::get('post_id');
        $limit   = 1;


		//--------------------------------------------//
		//"POST_Data"

		$sort_key  = 'post';
		$post_data = Model_Post::get_data($user_id, $sort_key, $post_id, $limit);


	    //----------------------------------------------//
	    // "Comments_data"

	   	$comment_data = Model_Comment::get_data($post_id);


	   	$data = array(
	   		"post" 		=> $post_data[0],
	   		"comments" 	=> $comment_data
	   	);


	   	$commentpage = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

	   	echo "$commentpage";


	}
}

