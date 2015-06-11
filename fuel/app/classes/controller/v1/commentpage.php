<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * commentpage api
 *
 */

class Controller_V1_Commentpage extends Controller_Rest
{

    public function action_index()
    {

        $post_id = Input::get('post_id');
        $user_id = Input::get('user_id');


		if (!empty($post_id)) {

			//--------------------------------------------//
			//"POST_Data"
			//--------------------------------------------//

			$post_data 	  = Model_Post::get_data($post_id);


	    	$post_user_id = $post_data[0]['post_user_id'];


	    	$like_num 	  = Model_Like::get_num($post_id);
	    	$post_data['0']['like_num']    = $like_num;

	    	$comment_num  = Model_Comment::get_num($post_id);
	    	$post_data['0']['comment_num'] = $comment_num;

	    	$follow_flag  = Model_Follow::get_flag($user_id, $post_user_id);
	    	$post_data['0']['follow_flag'] = $follow_flag;

	    	$like_flag	  = Model_Like::get_flag($user_id, $post_id);
	    	$post_data['0']['like_flag']   = $like_flag;


	    	//----------------------------------------------//
	    	// "Comments_data"
	    	//----------------------------------------------//

	    	$comment_data = Model_Comment::get_data($post_id);


	    	$data = array(
	    		"post" 		=> $post_data[0],
	    		"comments" 	=> $comment_data
	    	);


	    	$commentpage = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

	    	echo "$commentpage";

	    }
	}
}

