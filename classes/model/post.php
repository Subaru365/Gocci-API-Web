<?php
class Model_Post extends Model
{

	//"POST"取得
	public static function get_data(
		$user_id, $sort_key, $sort_id, $call = 0, $category_id = 0, $limit = 20)
	{
		$query = DB::select(
			'post_id', 'movie', 'thumbnail', 'category', 'tag', 'value',
			'memo', 'post_date', 'cheer_flag',
			'user_id', 'username', 'profile_img', 'rest_id', 'restname',
			DB::expr('X(lon_lat), Y(lon_lat)')
		)
		->from('posts')

		->join('restaurants', 'INNER')
		->on('post_rest_id', '=', 'rest_id')

		->join('users', 'INNER')
		->on('post_user_id', '=', 'user_id')

		->join('categories', 'LEFT OUTER')
		->on('post_category_id', '=', 'category_id')

		->join('tags', 'LEFT OUTER')
		->on('post_tag_id', '=', 'tag_id')

		->where('post_status_flag', '1')

		->order_by('post_date','desc')

		->limit($limit);


		//$sort_keyによる絞り込み

		if ($sort_key == 'all') {
			//何もしない。全て出力する。

		}elseif ($sort_key == 'post') {
			$query->where('post_id', $sort_id);

		}elseif ($sort_key == 'posts') {
			$query->where('post_id', 'in', $sort_id);

		}elseif ($sort_key == 'rest') {
			$query->where('post_rest_id', $sort_id);

		}elseif ($sort_key == 'user') {
			$query->where('user_id', $sort_id);

		}elseif ($sort_key == 'users') {
			$query->where('user_id', 'in', $sort_id);

		}else{
			error_log("Model_Post:${sort_key}が不正です。");
			exit;
		}

		//refine
		if ($category_id != 0) {
			$query->where('category_id', $category_id);
		}

		//next
		if ($call != 0) {
			$call_num = $call * $limit;
			$query->offset($call_num);
		}

		$post_data = $query->execute()->as_array();
		$post_num  = count($post_data);


		//---------------------------------------------------------------------//

		//付加情報格納(like_num, comment_num, want_flag, follow_flag, like_flag)


		for ($i=0; $i < $post_num; $i++) {

			$movie = $post_data[$i]['movie'];

			$post_data[$i]['mp4_movie']   = Model_Transcode::decode_mp4_movie($post_data[$i]['movie']);
			$post_data[$i]['movie']       = Model_Transcode::decode_hls_movie($post_data[$i]['movie']);
			$post_data[$i]['thumbnail']   = Model_Transcode::decode_thumbnail($post_data[$i]['thumbnail']);
			$post_data[$i]['profile_img'] = Model_Transcode::decode_profile_img($post_data[$i]['profile_img']);
			$post_data[$i]['share'] = 'mp4/' . "$movie" . '.mp4';


			$post_id	  = $post_data[$i]['post_id'];
			$post_user_id = $post_data[$i]['user_id'];
			$post_rest_id = $post_data[$i]['rest_id'];
			$post_date 	  = $post_data[$i]['post_date'];

	   		$post_data[$i]['gochi_num']   = Model_Gochi::get_num($post_id);
	   		$post_data[$i]['comment_num'] = Model_Comment::get_num($post_id);
	    	$post_data[$i]['want_flag']	  = Model_Want::get_flag($user_id, $post_rest_id);
	    	$post_data[$i]['follow_flag'] = Model_Follow::get_flag($user_id, $post_user_id);
	    	$post_data[$i]['gochi_flag']  = Model_Gochi::get_flag($user_id, $post_id);
			$post_data[$i]['post_date']   = Model_Date::get_data($post_date);
		}
		return $post_data;
	}


	public static function get_user($post_id)
	{
		$query = DB::select('post_user_id')->from('posts')
		->where('post_id', "$post_id");

		$post_user_id = $query->execute()->as_array();
		return $post_user_id[0]['post_user_id'];
	}


	public static function get_memo($post_id)
	{
		$query = DB::select('user_id', 'username', 'profile_img', 'memo', 'post_date')
		->from('posts')

		->join('users', 'INNER')
		->on('post_user_id', '=', 'user_id')

		->where('post_id', "$post_id");

		$value = $query->execute()->as_array();

		$key = array('comment_user_id', 'username', 'profile_img', 'comment', 'comment_date');
		$post_comment = array_combine($key, $value[0]);

		return $post_comment;
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

		$num = count($cheer_list);

		for ($i=0; $i < $num; $i++) {
			$cheer_list[$i]['profile_img'] =　Model_Transcode::decode_profile_img($cheer_list[$i]['profile_img']);
		}

		return $cheer_list;
	}


	//ユーザーに対する応援店数取得
	public static function get_user_cheer_num($user_id)
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


	//店舗に対する応援総数
	public static function get_rest_cheer_num($rest_id)
	{
		$query = DB::select('post_id')->from('posts')

		->where	   ('post_rest_id', "$rest_id")
		->and_where('cheer_flag', '1')
		->and_where('post_status_flag', '1');

		$result = $query->execute()->as_array();

		$cheer_num = count($result);
		return $cheer_num;
	}


	//動画投稿
	public static function post_data(
		$user_id, $rest_id, $movie_name, $category_id, $tag_id, $value, $memo, $cheer_flag)
	{
		$directory = explode('-', $movie_name);

		$movie     = "$directory[0]" . '/' . "$directory[1]" . '/'  . "$movie_name" . '_movie';
		$thumbnail = "$directory[0]" . '/' . "$directory[1]" . '/'  . '00002_' . "$movie_name" . '_img';


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


	//投稿を表示
	public static function post_publish($movie)
	{
		$query = DB::update('posts')
		->set  (array('post_status_flag' => '1'))
		->where('movie', "$movie");

		$result = $query->execute();
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
