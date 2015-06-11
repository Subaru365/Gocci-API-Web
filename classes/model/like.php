<?php
class Model_Like extends Model
{
	public static function get_num($post_id)
	{
		//クエリ文
		$query = DB::select('like_id')->from('likes');
		$query->where('like_post_id', "$post_id");


		$result = $query->execute()->as_array();
	   	$like_num = count($result);

		return $likes_num;
	}

}