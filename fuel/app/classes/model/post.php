<?php
class Model_Post extends Model
{

	//"POST"取得
	public static function get_data($user_id, $sort_key, $sort_id, $limit = 20)
	{
		$query = DB::select(
			'post_id', 'movie', 'thumbnail', 'category', 'tag', 'value',
			'memo', 'post_date', 'cheer_flag',
			'user_id', 'username', 'profile_img', 'rest_id', 'restname',
			DB::expr('X(lon_lat), Y(lon_lat)')
		)
		->from('posts')

		->join('restaurants', 'INNER')
		->on('posts.post_rest_id', '=', 'restaurants.rest_id')

		->join('users', 'INNER')
		->on('posts.post_user_id', '=', 'users.user_id')

		->join('categories', 'LEFT OUTER')
		->on('posts.post_category_id', '=', 'categories.category_id')

		->join('tags', 'LEFT OUTER')
		->on('posts.post_tag_id', '=', 'tags.tag_id')

		->where('post_status_flag', '1')

		->order_by('posts.post_date','desc')

		->limit("$limit");



		//$sort_keyによる絞り込み

		if ($sort_key == 'all') {
			//何もしない。全て出力する。
		}elseif ($sort_key == 'next') {
			$sort = $sort_id * $limit;
			$query->offset("$sort");

		}elseif ($sort_key == 'post') {
			$query->where('posts.post_id', "$sort_id");

		}elseif ($sort_key == 'rest') {
			$query->where('posts.post_rest_id', "$sort_id");

		}elseif ($sort_key == 'user') {
			$query->where('posts.post_user_id', "$sort_id");

		}else{
			error_log('Model_Post:$sort_keyが不正です。');
			exit;
		}

		//配列[comments]に格納

		$post_data = $query->execute()->as_array();
		$post_num  = count($post_data);



		//---------------------------------------------------------------------//

		//付加情報格納(like_num, comment_num, want_flag, follow_flag, like_flag)


		for ($i=0; $i < $post_num; $i++) {

			$post_id	  = $post_data[$i]['post_id'];
			$post_user_id = $post_data[$i]['user_id'];
			$post_rest_id = $post_data[$i]['rest_id'];
			$post_date 	  = $post_data[$i]['post_date'];


	   		$gochi_num 	  = Model_Gochi::get_num($post_id);
	   		$post_data[$i]['gochi_num']   = $gochi_num;

	    	$comment_num  = Model_Comment::get_num($post_id);
	   		$post_data[$i]['comment_num'] = $comment_num;

	    	$want_flag	  = Model_Want::get_flag($user_id, $post_rest_id);
	    	$post_data[$i]['want_flag']	  = $want_flag;

	    	$follow_flag  = Model_Follow::get_flag($user_id, $post_user_id);
	    	$post_data[$i]['follow_flag'] = $follow_flag;

	    	$gochi_flag	  = Model_Gochi::get_flag($user_id, $post_id);
	    	$post_data[$i]['gochi_flag']  = $gochi_flag;

	    	$date_diff 	  = Model_Date::get_data($post_date);
			$post_data[$i]['post_date']   = $date_diff;

		}

		return $post_data;
	}


	//1ユーザーが応援している店舗リスト
	public static function get_user_cheer($user_id)
	{
		$query = DB::select('rest_id', 'restname', 'locality')
		->from('posts')

		->join('restaurants', 'INNER')
		->on('post_rest_id', '=', 'rest_id')

		->where('post_user_id', "$user_id")
		->and_where('cheer_flag', '1')
		->and_where('post_status_flag', '1')

		->distinct(true);

		$cheer_list = $query->execute()->as_array();
		return $cheer_list;
	}


	//1店舗に対して応援しているユーザーリスト
	public static function get_rest_cheer($rest_id)
	{
		$query = DB::select('user_id', 'username', 'profile_img')
		->from('posts')

		->join('users', 'INNER')
		->on('post_user_id', '=', 'user_id')

		->where('post_rest_id', "$rest_id")
		->and_where('cheer_flag', '1')
		->and_where('post_status_flag', '1')

		->distinct(true);

		$cheer_list = $query->execute()->as_array();
		return $cheer_list;
	}


	//ユーザーに対する応援店数取得
	public static function cheer_num($user_id)
	{
		$query = DB::select('post_rest_id')->from('posts')

		->where	   ('post_user_id', "$user_id")
		->and_where('cheer_flag', '1')
		->and_where('post_status_flag', '1')

		->distinct(true);

		$result = $query->execute()->as_array();

		$cheer_num = count($result);
		return $cheer_num;
	}


	//動画投稿
	public static function post_data(
		$user_id, $rest_id, $movie_name,
		$category_id, $tag_id, $value, $memo, $cheer_flag)
	{
		$movie     = "$movie_name" . '-movie.ts';
		$thumbnail = '0002-' . "$movie_name" . '-img.png';


		$query = DB::insert('posts')
		->set(array(
			'post_user_id'      => "$user_id",
			'post_rest_id'      => "$rest_id",
			'movie'		        => "$movie",
			'thumbnail'         => "$thumbnail",
			'post_category_id'  => "$category_id",
			'post_tag_id'	    => "$tag_id",
			'value'        		=> "$value",
			'memo'         		=> "$memo",
			'cheer_flag'   		=> "$cheer_flag"
		))
		->execute();

		return $query;
	}


	//投稿を消去
	public static function post_delete($post_id)
	{
		$query = DB::update('posts')
		->set  (array('post_status_flag' => '0'))
		->where('post_id', "$post_id");

		$result = $query->execute();

		return $result;
	}

}