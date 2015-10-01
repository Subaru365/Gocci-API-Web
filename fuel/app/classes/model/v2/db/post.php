<?php
class Model_Post extends Model
{
	//"POST"取得
	public static function get_data()
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

		->limit(20);

		return $query;
	}


	public static function get_sort($query, $option)
	{
		//並び替え
		if ($option['order_id'] == 0) {
		//時系列
			$query->order_by('post_date','desc');


		} elseif ($option['order_id'] == 1) {
		//近い順
			$query->order_by(DB::expr("GLength(GeomFromText(CONCAT('LineString(${option['lon']} ${option['lat']},', X(lon_lat),' ', Y(lon_lat),')')))"));


		} elseif ($option['order_id'] == 2) {
		//Gochi!ランキング

			//対象となる投稿の期間($interval)
			$interval = date("Y-m-d",strtotime("-1 month"));
			$now_date = date("Y-m-d",strtotime("+1 day"));

			$query->join('gochis', 'RIGHT')
			->on('gochi_post_id', '=', 'post_id')

			->where	   ('gochi_date', 'BETWEEN', array("$interval", "$now_date"))

			->group_by('gochi_post_id')
			->order_by(DB::expr('COUNT(gochi_post_id)'), 'desc');
		}


		//カテゴリー絞り込み
		if ($option['category_id'] != 0) {
			$query->where('category_id', $option['category_id']);
		}


		//価格絞り込み
		if ($option['value_id'] != 0) {
			if ($option['value_id'] == 1) {
				$query->where('value', 'between', array(1, 700));
			}
			if ($option['value_id'] == 2) {
				$query->where('value', 'between', array(500, 1500));
			}
			if ($option['value_id'] == 3) {
				$query->where('value', 'between', array(1500, 5000));
			}
			if ($option['value_id'] == 4) {
				$query->where('value', '>', 3000);
			}
		}


		//次ページ読み込み
		if ($option['call'] != 0) {
			$call_num = $option['call'] * $limit;
			$query->offset($call_num);
		}


		$query ->order_by('post_date','desc');
		$post_data = $query->execute()->as_array();

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

		$re_user = array();
		array_push ($value[0], $re_user);

		$key = array('comment_user_id', 'username', 'profile_img', 'comment', 'comment_date', 're_user');
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
