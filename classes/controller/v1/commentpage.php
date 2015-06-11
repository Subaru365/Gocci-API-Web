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
	    	$comments_num = Model_Comment::get_num($post_id);






	    	//----------------------------------------------------//
	    	//フォローしてるかを判断するメソッド
	   		//[$user_id,$post_user_id]->($follow_flag)
	   		//0=フォローしてない。1=フォローなう。

	    	$query = DB::query(
	    	"SELECT follow_id
	    	 FROM   follows
	   		 WHERE  follow_a_user_id = $user_id
	   		 AND    follow_p_user_id = $post_user_id;"
	   		);

			$num = $query->execute()->as_array();

			if ($num == true) {
				$follow_flag = 1;
			}else{
				$follow_flag = 0;
			}

			//--debug--//
			//echo "$follow_flag";


			//-----------------------------------------------------//
	    	//いいねをしてるかを判断するメソッド
	    	//[$post_user_id,$user_id]->($like_flag)
			//0=いいねしてない。1=いいねしてる。

	    	$query = DB::query(
	    	"SELECT like_id
	    	 FROM   likes
	   		 WHERE  like_user_id = $user_id
	   		 AND    like_post_id = $post_id;"
	   		);

			$num = $query->execute()->as_array();

			//print_r($num);

			if ($num == true) {
				$like_flag = 1;
			}else{
				$like_flag = 0;
			}

			//--debug--//
			//echo "$like_flag[$i]";


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

