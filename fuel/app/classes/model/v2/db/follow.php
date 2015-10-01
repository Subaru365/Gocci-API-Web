<?php

class Model_Follow extends Model
{
	//自分がフォローしてるかフラグで返す
	public static function get_flag($target_user_id)
	{
		$query = DB::select('follow_id')
		->from     ('follows')
		->where    ('follow_a_user_id', session::get('user_id')
		->and_where('follow_p_user_id', "$target_user_id");

		$result = $query->execute()->as_array();


		if (empty($result)) {
			$follow_flag = 0;
		}else{
			$follow_flag = 1;
		}

		return $follow_flag;
	}


	//followしているuser_idリスト
	public static function get_follow_id()
	{
		$query = DB::select('follow_p_user_id')
		->from('follows')
		->where('follow_a_user_id', session::get('user_id'));

		$follow_id = $query->execute()->as_array();

		if (empty($follow_id)) {
			Controller_V1_Mobile_Base::output_error(301);
			exit;
		}

		return $follow_id;
	}


	//followしているユーザー情報
	public static function get_follow($target_user_id)
	{
		$query = DB::select('user_id', 'username', 'profile_img')
		->from ('follows')
		->join ('users', 'INNER')
		->on   ('follow_p_user_id', '=', 'user_id')
		->where('follow_a_user_id', "$target_user_id");

		$follow_list = $query->execute()->as_array();

		$follow_num = count($follow_list);

		for ($i=0; $i < $follow_num; $i++) {
			$follow_list[$i]['profile_img'] = Model_Transcode::decode_profile_img($follow_list[$i]['profile_img']);
			$follow_list[$i]['follow_flag'] = self::get_flag($follow_list[$i]['user_id']);
		}

		return $follow_list;
	}


	//フォローされてるユーザー情報
	public static function get_follower($user_id, $target_user_id)
	{
		$query = DB::select('user_id', 'username', 'profile_img')
		->from ('follows')
		->join ('users', 'INNER')
		->on   ('follow_a_user_id', '=', 'user_id')
		->where('follow_p_user_id', "$target_user_id");

		$follower_list = $query->execute()->as_array();

		$follower_num = count($follower_list);

		for ($i=0; $i < $follower_num; $i++) {
			$follower_list[$i]['profile_img'] = Model_Transcode::decode_profile_img($follower_list[$i]['profile_img']);
			$follower_list[$i]['follow_flag'] = self::get_flag($follower_list[$i]['user_id']);
		}

		return $follower_list;
	}


	//===================================================================//


	//フォロー数を返す
	public static function follow_num($user_id)
	{
		$query = DB::select('follow_id')
		->from ('follows')
		->where('follow_a_user_id', "$user_id");

		$result = $query->execute()->as_array();

		$follow_num = count($result);
		return $follow_num;
	}


	//フォロワー数を返す
	public static function follower_num($user_id)
	{
		$query = DB::select('follow_id')
		->from ('follows')
		->where('follow_p_user_id', "$user_id");

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