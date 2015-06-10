<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * commentpage api
 *
 */

class Controller_V1_Commentpage extends Controller_Rest
{
	protected $format = 'json';

    public function action_index()
    {

        $post_id = Input::get('post_id');
        $user_id = Input::get('user_id');


		if (!empty($post_id)) {

			//--------------------------------------------//
			//"POST_Data"
			//--------------------------------------------//

			//[post_id]->($post_data)

	    	$query = DB::query(
	    	"SELECT
	    	 p.post_id, p.post_user_id, u.username,
	    	 u.profile_img, u.cover_img, p.post_rest_id, r.restname,
			 p.movie, p.thumbnail, p.cheer_flag, p.post_date,
			 c.category, t.tag, p.value, p.memo

			 FROM posts as p

			 JOIN restaurants as r
			 ON p.post_rest_id = r.rest_id

			 JOIN users as u
			 ON p.post_user_id = u.user_id

			 LEFT JOIN categories as c
			 ON p.post_category_id = c.category_id

			 LEFT JOIN tags as t
			 ON p.post_tag_id = t.tag_id

			 WHERE p.post_id = $post_id;"
			);

	    	$post_data = $query->execute()->as_array();

	    	//--debug--//
		    //print_r($post_data);


			//-------------------------------------------//
	    	$post_user_id = $post_data[0]['post_user_id'];
	    	//-------------------------------------------//



	    	//$like_numに各like数を格納
	    	$query = DB::query(
	    	"SELECT like_id
	   		 FROM   likes
	   		 WHERE  like_post_id = $post_id;"
	   		);

	    	$num = $query->execute()->as_array();
	   		$like_num = count($num);

	   		//--debug--//
	   		//echo "イイね数" . "$like_num[$i]" . "\n";


	    	//$comment_numに各comment数を格納
	    	$query = DB::query(
	    	"SELECT comment_id
	   		 FROM   comments
	   		 WHERE  comment_post_id = $post_id;"
	   		);

	   		$num = $query->execute()->as_array();
	   		$comment_num = count($num);

	   		//--debug--//
	   		//echo "コメント数" . "$comment_num[$i]" . "\n";



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

	    	$post_data[0]['like_num']    = $like_num;
	    	$post_data[0]['comment_num'] = $comment_num;
	    	$post_data[0]['follow_flag'] = $follow_flag;
	    	$post_data[0]['like_flag']   = $like_flag;

	    	$rows = array("post" => $post_data[0]);
	    	$post_data = json_encode($rows , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );
	    	echo "$post_data";




	    	//----------------------------------------------//
	    	// "Comments_data"
	    	//----------------------------------------------//

	    	$query = DB::query(
	    	"SELECT
	    	 c.comment_user_id, u.username, u.profile_img,
	    	 c.comment, c.comment_date

	    	 FROM comments as c

	    	 JOIN users as u
	    	 ON c.comment_user_id = u.user_id

	    	 WHERE c.comment_post_id = $post_id;"
	    	);

	    	$comments_data = $query->execute()->as_array();

	    	$rows = array("comments" => $comments_data);
	    	$comments_data = json_encode($rows , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );
	    	echo "$comments_data";


	    }
	}
}

