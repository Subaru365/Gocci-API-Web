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

	   	//--debug--//
	   	//echo "$likes_num";


		return $like_num;
	}


	public static function get_flag($user_id, $post_id)
	{
		//クエリ文
		$query = DB::select('like_id')->from('likes');
		$query->where	 ('like_user_id', "$user_id");
		$query->and_where('like_post_id', "$post_id");


		$result = $query->execute()->as_array();


		if ($result == true) {
			$like_flag = 1;
		}else{
			$like_flag = 0;
		}

		//--debug--//
		//echo "$like_flag";


		return $like_flag;
	}


	public static function get_rank($limit)
	{

		$now_date = date("Y-m-d");
		$interval = date("Y-m-d",strtotime("-1 month"));

		$query = DB::select('like_post_id')->from('likes');
		$query->where	('like_date', 'BETWEEN', array("$interval", "$now_date"));
		$query->group_by('like_post_id');
		$query->order_by(DB::expr('COUNT(like_id)'), 'desc');
		$query->limit   ("$limit");

		/*
		select like_post_id, count(*) as cnt, like_date from likes
		WHERE like_date BETWEEN '2015-05-13' AND now()
		group by like_post_id
		order by count(*) desc
		limit 10
		*/

		$result = $query->execute()->as_array();
		return $result;

	}
}