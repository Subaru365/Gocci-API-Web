<?php

class Model_Follow extends Model
{

	public static function get_flag($user_id, $post_user_id)
	//フォローしてるかを判断する
	{
		$query = DB::select('follow_id')->from('follows')

		->where 	('follow_a_user_id', "$user_id")
		->and_where ('follow_p_user_id', "$post_user_id");

		$result = $query->execute()->as_array();


		if ($result == true) {
			$follow_flag = 1;
		}else{
			$follow_flag = 0;
		}


		//--debug--//
		//echo "$follow_flag";

		return $follow_flag;
	}


	public static function follow_num($user_id)
	//フォロー数を返す
	{
		$query = DB::select('follow_id')->from('follows')
		->where	('follow_a_user_id', "$user_id");

		$result = $query->execute()->as_array();


		$follow_num = count($result);
		return $follow_num;
	}


	public static function follower_num($user_id)
	//フォロワー数を返す
	{
		$query = DB::select('follow_id')->from('follows')
		->where ('follow_p_user_id', "$user_id");

		$result = $query->execute()->as_array();


		$follower_num = count($result);
		return $follower_num;
	}
}