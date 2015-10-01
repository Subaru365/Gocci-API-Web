<?php

class Model_Comment extends Model
{
	//コメント数取得
	public static function get_num($post_id)
	{
		$query = DB::select('comment_id')->from('comments')
		->where('comment_post_id', "$post_id");

		$result = $query->execute()->as_array();
	   	$comment_num = count($result);

		return $comment_num;
	}


	public static function get_data($post_id)
	{
		$query = DB::select(
			'comment_id', 'comment_user_id', 'username',
			'profile_img', 'comment', 'comment_date')
		->from('comments')

		->join('users', 'INNER')
		->on('comment_user_id', '=', 'user_id')

		->where('comment_post_id', "$post_id");

		$comment_data = $query->execute()->as_array();

		$num = count($comment_data);

		for ($i=0; $i < $num; $i++) {

			$comment_data[$i]['profile_img'] =
				Model_Transcode::decode_profile_img($comment_data[$i]['profile_img']);

			$comment_data[$i]['comment_date'] =
				Model_Date::get_data($comment_data[$i]['comment_date']);
		}

		return $comment_data;
	}


	//コメント登録
	public static function post_comment($user_id, $post_id, $comment)
	{
		$query = DB::insert('comments')
		->set(array(
			'comment_user_id' => "$user_id",
			'comment_post_id' => "$post_id",
			'comment' 	      => "$comment"
		))
		->execute();

		return $query[0];
	}
}