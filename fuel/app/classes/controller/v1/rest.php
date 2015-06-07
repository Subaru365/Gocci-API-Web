<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * rest api
 * 
 */
class Controller_V1_Rest extends Controller_Rest
{
    // beforeメソッドでログイン認証チェック

    // privateにするとエラーになる
    protected $format = 'json';

    public function action_index()
    {
		$limit = Input::get('limit');

		if (empty($limit)) {
		    $limit = 30;
		}

        $rest_id = Input::get('rest_id');
        $user_id = Input::get('user_id');

		if (!empty($rest_id)) {

			//--------------------------------------------//
			//"POST_Data"
			//--------------------------------------------//

			//rest_idを元に、SELECTデータを$postに代入

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

			 WHERE p.post_rest_id = $rest_id;"
			);

	    	$posts = $query->execute()->as_array();

	    	//--debug--//
	    	//print_r($posts);


	    	//---------------------------------------------//
			//$post_numにPOSTの数＋１を代入

	    	$post_num = count($posts);

	    	//--debug--//
	    	//print_r($post_num);


	    	//--------------------------------------------//
	    	//$post_idに各post_idを格納
	    	//$post_user_idに各post_user_idを格納

	    	for ($i=0; $i < $post_num; $i++) {

	    		$post_id[$i] = $posts[$i]['post_id'];
	    		$post_user_id[$i] = $posts[$i]['post_user_id'];

	    		//--debug--//
	    		//echo "$post_id[$i]";
	    		//echo "$post_user_id[$i]";
	    	}


	    	//--------------------------------------------//

	    	for ($i=0; $i < $post_num; $i++) {


	    		//----------------------------------------------------//
	    		//たぶん以下はクラス化するべき！
	    		//[$post_idと$post_numを渡したら、
	    		//それぞれ $like_num,$comment_numが返ってくる]みたいな。。


	    		//$like_numに各like数を格納
	    		$query = DB::query(
	    		"SELECT like_id
	    		 FROM   likes
	    		 WHERE  like_post_id = $post_id[$i];"
	    		);

	    		$num = $query->execute()->as_array();
	    		$like_num[$i] = count($num);

	    		//--debug--//
	    		//echo "イイね数" . "$like_num[$i]" . "\n";


	    		//$comment_numに各comment数を格納
	    		$query = DB::query(
	    		"SELECT comment_id
	    		 FROM   comments
	    		 WHERE  comment_post_id = $post_id[$i];"
	    		);

	    		$num = $query->execute()->as_array();
	    		$comment_num[$i] = count($num);

	    		//--debug--//
	    		//echo "コメント数" . "$comment_num[$i]" . "\n";


	    		//----------------------------------------------------//
	    		//ここもクラス化するべき!?
	    		//[$post_user_idと$user_idを渡すと、follow_flagが返る]

	    		$query = DB::query(
	    		"SELECT follow_id
	    		 FROM   follows
	    		 WHERE  follow_a_user_id = $user_id
	    		 AND    follow_p_user_id = $post_user_id[$i];"
	    		);

				$num = $query->execute()->as_array();

				//print_r($num);

				if ($num == true) {
					$follow_flag[$i] = 1;
				}else{
					$follow_flag[$i] = 0;
				}

				//--debug--//
				//echo "$follow_flag[$i]";


	    		//-----------------------------------------------------//
	    		//ここもクラス化するべき!?
	    		//[$post_user_idと$user_idを渡すと、follow_flagが返る]

	    		$query = DB::query(
	    		"SELECT like_id
	    		 FROM   likes
	    		 WHERE  like_user_id = $user_id
	    		 AND    like_post_id = $post_id[$i];"
	    		);

				$num = $query->execute()->as_array();

				//print_r($num);

				if ($num == true) {
					$like_flag[$i] = 1;
				}else{
					$like_flag[$i] = 0;
				}

				//--debug--//
				//echo "$follow_flag[$i]";
	    	}


	    	//--------------------------------------------//
			//"Rest_Data"
			//--------------------------------------------//

			//$rest_dataに店舗情報を格納

			$query = DB::query(
			"SELECT rest_id,
			 restname, locality, lat, lon, tell,
			 homepage, rest_category

			 FROM  restaurants
			 WHERE rest_id = $rest_id;"
			);

			$rest_data = $query->execute()->as_array();

			//--debug--//
			// print_r($rest_data[0]);

			//--------------------------------------------//
			//店舗に対するcheer数を返すメソッド

	    	$query = DB::query(
	    	"SELECT cheer_flag
	   		 FROM   posts
	   		 WHERE  post_rest_id = $rest_id
	   		 AND    cheer_flag = 1;"
	   		);

	    	$cheers = $query->execute()->as_array();
	    	$cheer_num = count($cheers);

	    	//--debug--//
	    	//echo "$cheer_num";


	    	//---------------------------------------------//
	    	//店舗に対する行きたいに登録してるかを返すメソッド

	    	$query = DB::query(
	    	"SELECT want_flag
	    	 FROM	wants
	    	 WHERE  want_user_id = $user_id
	    	 AND	want_rest_id = $rest_id
	    	 AND	want_flag	 = 1"
	    	);

	    	$want = $query->execute()->as_array();
	    	$want_flag = count($want);

	    	//--debug--//
	    	//print_r($want_flag);

	    	//---------------------------------------------//

	    	$rest_data['0']['rest_cheer_num'] = $cheer_num;
	    	$rest_data['0']['want_flag']  	  = $want_flag;
	    	$rows = array("restaurants" => $rest_data[0]);
	    	$rest_data = json_encode($rows , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE );
	    	echo "$rest_data";
	
	    	//---------------------------------------------//

	    	for ($i=0; $i < $post_num; $i++) {
	    		$posts[$i]['like_num'] = $like_num[$i];
	    		$posts[$i]['comment_num'] = $comment_num[$i];
	    		$posts[$i]['follow_flag'] = $follow_flag[$i];
	    		$posts[$i]['like_flag']	  = $like_flag[$i];
	    	}

	    	$rows = array("posts" => $posts);
	    	$post_date = json_encode($rows , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE );
	    	echo "$post_date";
	    }
	}
}
