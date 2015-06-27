<?php

class Model_Follow extends Model
{

	//相手のユーザーに対してフォローしてるかフラグで返す
	public static function get_flag($user_id, $target_user_id)
	{
		$query = DB::select('follow_id')->from('follows')

		->where 	('follow_a_user_id', "$user_id")
		->and_where ('follow_p_user_id', "$target_user_id");

		$result = $query->execute()->as_array();


		if ($result == true) {
			$follow_flag = 1;
		}else{
			$follow_flag = 0;
		}

		return $follow_flag;
	}


	//フォロー数を返す
	public static function follow_num($user_id)
	{
		$query = DB::select('follow_id')->from('follows')
		->where	('follow_a_user_id', "$user_id");

		$result = $query->execute()->as_array();


		$follow_num = count($result);
		return $follow_num;
	}


	//フォロワー数を返す
	public static function follower_num($user_id)
	{
		$query = DB::select('follow_id')->from('follows')
		->where ('follow_p_user_id', "$user_id");

		$result = $query->execute()->as_array();


		$follower_num = count($result);
		return $follower_num;
	}


	//フォロー登録
	public static function post_follow($user_id, $target_user_id)
	{
		$query = DB::insert('follows')
		->set(array(
			'follow_a_user_id' => "$user_id",
			'follow_p_user_id' => "$target_user_id"
		));

		$result = $query->execute();

		return $result;
	}


	//フォロー解除
	public static function post_unfollow($user_id, $target_user_id)
	{
		$query = DB::delete('follows')
		->where     ('follow_a_user_id', "$user_id")
		->and_where ('follow_p_user_id', "$target_user_id");

		$result = $query->execute();

		return $result;
	}

}