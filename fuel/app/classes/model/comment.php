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


		//--debug--//
		//echo "$data";

		return $data;
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