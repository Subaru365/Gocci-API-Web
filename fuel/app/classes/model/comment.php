<?php
class Model_Comment extends Model
{

	public static function get_data($post_id)
	{

		//クエリ文
		$query = DB::select(
			'comments.comment_user_id', 'users.username',
			'users.profile_img', 'comments.comment',
			'comments.comment_date')

		->from('comments')

		->where('comments.comment_post_id', "$post_id")

		->join('users', 'INNER')
		->on('comments.comment_user_id', '=', 'users.user_id');

		$comment_data = $query->execute()->as_array();



		$comment_num  = count($comment_data);

		for ($i=0; $i < $comment_num; $i++) {

			//日付情報を現在との差分に書き換え

			$comment_date = $comment_data[$i]['comment_date'];

			$date_diff 	　= Model_Date::get_data($comment_date);
			$comment_data[$i]['comment_date'] = $date_diff;

		}

		//--debug--//
		//echo "$comment_data";

		return $comment_data;
	}



	public static function get_num($post_id)
	{
		//クエリ文
		$query = DB::select('comment_id')->from('comments');
		$query->where('comment_post_id', "$post_id");


		$result = $query->execute()->as_array();
	   	$comment_num = count($result);


	   	//--debug--//
	   	//echo "$comment_num";

		return $comment_num;
	}

}