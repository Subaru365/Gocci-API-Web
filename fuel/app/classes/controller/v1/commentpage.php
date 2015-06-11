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

			$data = Model_Post::get_data($post_id);


			//-------------------------------------------//
	    	$post_user_id = $data['post']['post_user_id'];
	    	//-------------------------------------------//


	    	$like_num 	  = Model_Like::get_num($post_id);

	    	$comment_num  = Model_Comment::get_num($post_id);

	    	$follow_flag  = Model_Follow::get_flag($user_id, $post_user_id);

	    	$like_flag	  = Model_Like::get_flag($user_id, $post_id);


			//---------------------------------------------//
	    	//投稿情報吐き出し

	    	$data['post']['like_num']    = $like_num;
	    	$data['post']['comment_num'] = $comment_num;
	    	$data['post']['follow_flag'] = $follow_flag;
	    	$data['post']['like_flag']   = $like_flag;

	    	$post_data = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );
	    	echo "$post_data";




	    	//----------------------------------------------//
	    	// "Comments_data"
	    	//----------------------------------------------//

	    	$data = Model_Comment::get_data($post_id);

			$comments_data = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

	    	echo "$comments_data";

	    }
	}
}

