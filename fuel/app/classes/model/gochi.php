<?php

class Model_Gochi extends Model
{

	//１投稿対するgochi数を求める
	public static function get_num($post_id)
	{

		$query = DB::select('gochi_id')->from('gochis')
		->where('gochi_post_id', "$post_id");

		$result   = $query->execute()->as_array();
	   	$gochi_num = count($result);

		return $gochi_num;
	}



	//１投稿に対し自分がgochiしているかを求める
	public static function get_flag($user_id, $post_id)
	{

		$query = DB::select('gochi_id')->from('gochis')
		->where 	('gochi_user_id', "$user_id")
		->and_where ('gochi_post_id', "$post_id");

		$result = $query->execute()->as_array();


		if ($result == true) {
			$gochi_flag = 1;
		}else{
			$gochi_flag = 0;
		}

		return $gochi_flag;
	}



	//gochi順に投稿を格納する
	public static function get_rank($call_num = 0, $limit = 3)
	{
		//対象となる投稿の期間($interval)
		$now_date = date("Y-m-d",strtotime("+1 day"));
		$interval = date("Y-m-d",strtotime("-3 month"));


		$query = DB::select('post_id')->from('gochis')
		->where	   ('gochi_date', 'BETWEEN', array("$interval", "$now_date"))
		->and_where('post_status_flag', '1')

		->join('posts', 'INNER')
		->on('gochi_post_id', '=', 'post_id')

		->group_by('gochi_post_id')

		->order_by(DB::expr('COUNT(gochi_id)'), 'desc')
		->order_by('post_date', 'desc')

		->limit("$limit");

		if ($call_num != 0) {
			$sort = $call_num * $limit;
			$query->offset("$sort");
		}

		$result = $query->execute()->as_array();

		return $result;
	}



	//gochi登録
	public static function post_gochi($user_id, $post_id)
	{

		$query = DB::insert('gochis')
		->set(array(
			'gochi_user_id' => "$user_id",
			'gochi_post_id' => "$post_id"
		))
		->execute();

		$query = DB::select('post_user_id')->from('posts')
		->where('post_id', "$post_id");

		$post_user_id = $query->execute()->as_array();

		return $post_user_id[0]['post_user_id'];
	}

}