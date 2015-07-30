<?php
class Model_Comment extends Model
{

	public static function get_data($post_id)
	{
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

			$date_diff    = Model_Date::get_data($comment_date);
			$comment_data[$i]['comment_date'] = $date_diff;
		}

		return $comment_data;
	}


	//コメント数取得
	public static function get_num($post_id)
	{
		$query = DB::select('comment_id')->from('comments')
		->where('comment_post_id', "$post_id");

		$result = $query->execute()->as_array();
	   	$comment_num = count($result);

		return $comment_num;
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

		$query = DB::select('post_user_id')->from('posts')
		->where('post_id', "$post_id");

		$post_user_id = $query->execute()->as_array();

		return $post_user_id[0]['post_user_id'];
	}
}