<?php

class Model_Like extends Model
{

	//１投稿対するgochi数を求める
	public static function get_num($post_id)
	{

		$query = DB::select('like_id')->from('likes')
		->where('like_post_id', "$post_id");

		$result   = $query->execute()->as_array();
	   	$like_num = count($result);

		return $like_num;
	}



	//１投稿に対し自分がgochiしているかを求める
	public static function get_flag($user_id, $post_id)
	{

		$query = DB::select('like_id')->from('likes')
		->where 	('like_user_id', "$user_id")
		->and_where ('like_post_id', "$post_id");

		$result = $query->execute()->as_array();


		if ($result == true) {
			$like_flag = 1;
		}else{
			$like_flag = 0;
		}

		return $like_flag;
	}



	//gochi順に投稿を格納する
	public static function get_rank($limit)
	{
		//対象となる投稿の期間($interval)
		$now_date = date("Y-m-d");
		$interval = date("Y-m-d",strtotime("-1 month"));


		$query = DB::select('like_post_id')->from('likes')
		->where	   ('like_date', 'BETWEEN', array("$interval", "$now_date"))
		->group_by ('like_post_id')
		->order_by (DB::expr('COUNT(like_id)'), 'desc')
		->limit    ("$limit");

		$result = $query->execute()->as_array();

		return $result;
	}



	//gochi登録
	public static function post_gochi($user_id, $post_id)
	{

		$query = DB::insert('likes')
		->set(array(
			'like_user_id' => "$user_id",
			'like_post_id' => "$post_id"
		))
		->execute();

		$query = DB::select('post_user_id')->from('posts')
		->where('post_id', "$post_id");

		$post_user_id = $query->$execute()->$as_array();

		return $post_user_id[0]['post_user_id'];
	}

}