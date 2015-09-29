<?php

class Model_Gochi extends Model
{
	//１投稿のgochi数を求める
	public static function get_num($post_id)
	{
		$query = DB::select('gochi_id')
		->from ('gochis')
		->where('gochi_post_id', "$post_id");

		$result    = $query->execute()->as_array();
	   	$gochi_num = count($result);

		return $gochi_num;
	}


	//１投稿に対して自分がgochiしているかを求める
	public static function get_flag($post_id)
	{
		$query = DB::select('gochi_id')
		->from     ('gochis')
		->where    ('gochi_user_id', session::get('user_id'))
		->and_where('gochi_post_id', "$post_id");

		$result = $query->execute()->as_array();


		if ($result == true) {
			$gochi_flag = 1;
		}else{
			$gochi_flag = 0;
		}

		return $gochi_flag;
	}

	//================//


	//gochi登録
	public static function post_gochi($user_id, $post_id)
	{
		$query = DB::insert('gochis')
		->set(array(
			'gochi_user_id' => "$user_id",
			'gochi_post_id' => "$post_id"
		))
		->execute();


		$query = DB::select('post_user_id')
		->from ('posts')
		->where('post_id', "$post_id");

		$post_user_id = $query->execute()->as_array();

		return $post_user_id[0]['post_user_id'];
	}
}