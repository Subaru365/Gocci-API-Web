<?php
class Model_Comment extends Model
{
	public static function get_data($post_id)
	{
		//クエリ文
		$query = DB::select('comments.comment_user_id', 'users.username', 'users.profile_img', 'comments.comment', 'comments.comment_date')->from('comments');

		$query->where('comments.comment_post_id', "$post_id");

		$query->join('users', 'INNER');
		$query->on('comments.comment_user_id', '=', 'users.user_id');


		//配列[comments]に格納
		$result = $query->execute()->as_array();
		$data = array("comments" => $result);

		return $data;
	}

}