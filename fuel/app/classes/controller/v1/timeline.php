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
		    $limit = 30;
		}


		if (!empty($user_id)) {

			//--------------------------------------------//
			//"POST_Data"
			//--------------------------------------------//

			$post_data 	  = Model_Post::get_all($limit);


			for ($i=0; $i < $limit; $i++) {

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

	    	$timelinepage = json_encode($post_data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

	    	echo "$timelinepage";

	    }
	}
}

