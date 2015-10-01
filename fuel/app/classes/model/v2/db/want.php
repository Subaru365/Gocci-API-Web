<?php

class Model_Want extends Model
{
	//自分が行きたい店に登録しているかフラグで返す
	public static function get_flag($post_rest_id)
	{
		$query = DB::select('want_id')->from('wants')
		->where 	('want_user_id', session::get('user_id'))
		->and_where ('want_rest_id', "$post_rest_id");

		$result = $query->execute()->as_array();


		if (empty($result)) {
			$want_flag = 0;
		}else{
			$want_flag = 1;
		}

		return $want_flag;
	}


	//==============================================//


	public static function get_want($user_id)
	{
		$query = DB::select(
			'rest_id', 'restname', 'locality'
		)
		->from('wants')

		->join('restaurants', 'INNER')
		->on('want_rest_id', '=', 'rest_id')

		->where('want_user_id', "$user_id");

		$want_list = $query->execute()->as_array();
		return $want_list;
	}


	public static function want_num($user_id)
	{
		$query = DB::select('want_id')->from('wants')
		->where('want_user_id', "$user_id");

		$result = $query->execute()->as_array();


		$want_num = count($result);
		return $want_num;
	}


	//行きたい登録
	public static function post_want($user_id, $want_rest_id)
	{
		$query = DB::insert('wants')
		->set(array(
			'want_user_id' => "$user_id",
			'want_rest_id' => "$want_rest_id"
		));

		$result = $query->execute();
		return $result;
	}


	//行きたい解除
	public static function post_unwant($user_id, $want_rest_id)
	{
		$query = DB::delete('wants')
		->where     ('want_user_id', "$user_id")
		->and_where ('want_rest_id', "$want_rest_id");

		$result = $query->execute();
		return $result;
	}
}