<?php
class Model_Post extends Model
{

	public static function get_data($user_id, $sort_key, $sort_id, $limit)
	{

		//クエリ文

		$query = DB::select(
			'posts.post_id', 'posts.post_user_id', 'users.username',
			'users.profile_img', 'posts.post_rest_id', 'restaurants.restname',
			'posts.movie', 'posts.thumbnail', 'categories.category', 'tags.tag',
			'posts.value', 'posts.memo', 'posts.post_date', 'posts.cheer_flag'
		)->from('posts');

		$query->join('restaurants', 'INNER');
		$query->on('posts.post_rest_id', '=', 'restaurants.rest_id');

		$query->join('users', 'INNER');
		$query->on('posts.post_user_id', '=', 'users.user_id');

		$query->join('categories', 'LEFT OUTER');
		$query->on('posts.post_category_id', '=', 'categories.category_id');

		$query->join('tags', 'LEFT OUTER');
		$query->on('posts.post_tag_id', '=', 'tags.tag_id');

		$query->order_by('posts.post_date','desc');

		$query->limit("$limit");



		//$sort_keyによる絞り込み

		if ($sort_key == 'all') {
			//何もしない。全て出力する。

		}elseif ($sort_key == 'post') {
			$query->where('posts.post_id', "$sort_id");

		}elseif ($sort_key == 'rest') {
			$query->where('posts.post_rest_id', "$sort_id");

		}else{
			error_log('Model_Post:$sort_keyが不正です。');
			exit;
		}

		//配列[comments]に格納

		$post_data = $query->execute()->as_array();
		$post_num  = count($post_data);

		//---------------------------------------------------------------------//


		for ($i=0; $i < $post_num; $i++) {

			//付加情報格納(like_num, comment_num, want_flag, follow_flag, like_flag)

			$post_id	  = $post_data[$i]['post_id'];
			$post_user_id = $post_data[$i]['post_user_id'];
			$post_rest_id = $post_data[$i]['post_rest_id'];
			$post_date 	  = $post_data[$i]['post_date'];


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

	    	$date_diff 	  = Model_Date::get_data($post_date);
			$post_data[$i]['post_date']   = $date_diff;


	    	/*

	    	//post_date表示形式 変換

	    	$datetime1 = new DateTime("$post_date");
			$datetime2 = new DateTime(date('Y-m-d H:i:s'));

			$interval = $datetime1->diff($datetime2);

			if ($interval->format('%y') > 0) {
				$date_diff = $interval->format('%y') . '年前';

			}elseif ($interval->format('%m') > 0) {
				$date_diff = $interval->format('%m') . 'ヶ月前';

			}elseif ($interval->format('%d') > 0) {
				$date_diff = $interval->format('%d') . '日前';

			}elseif ($interval->format('%h') > 0) {
				$date_diff = $interval->format('%h') . '時間前';

			}elseif ($interval->format('%i') > 0) {
				$date_diff = $interval->format('%i') . '分前';

			}elseif ($interval->format('%s') > 0) {
				$date_diff = $interval->format('%s') . '秒前';

			}else{
				$date_diff = '未来から';
				error_log('$post_dateの時刻エラー');
			}
			*/

		}

		return $post_data;
	}

}