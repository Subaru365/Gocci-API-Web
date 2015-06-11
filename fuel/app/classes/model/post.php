<?php
class Model_Post extends Model
{

	public static function get_all($limit)
	{
		$query = DB::select(
			'posts.post_id', 'posts.post_user_id', 'users.username',
			'users.profile_img', 'posts.post_rest_id', 'restaurants.restname',
			'posts.movie', 'posts.thumbnail', 'categories.category', 'tags.tag',
			'posts.value', 'posts.memo', 'posts.post_date', 'posts.cheer_flag'
		)->from('posts');

		$query->limit("$limit");

		$query->join('restaurants', 'INNER');
		$query->on('posts.post_rest_id', '=', 'restaurants.rest_id');

		$query->join('users', 'INNER');
		$query->on('posts.post_user_id', '=', 'users.user_id');

		$query->join('categories', 'LEFT OUTER');
		$query->on('posts.post_category_id', '=', 'categories.category_id');

		$query->join('tags', 'LEFT OUTER');
		$query->on('posts.post_tag_id', '=', 'tags.tag_id');

		$query->order_by('posts.post_date','desc');


		//配列[comments]に格納
		$post_data = $query->execute()->as_array();


		//--debug--//
		//echo "$data";

		return $post_data;
	}






	public static function get_data($sort_key, $post_id)
	{
		if ($sort_key == 'post') {
			$where_key = 'posts.post_id';

		}elseif ($sort_key == 'rest') {
			$where_key = 'posts.post_rest_id';

		}else{
			return $post_data['error'] = 1;
		}


		//クエリ文
		$query = DB::select(
			'posts.post_id', 'posts.post_user_id', 'users.username',
			'users.profile_img', 'posts.post_rest_id', 'restaurants.restname',
			'posts.movie', 'posts.thumbnail', 'categories.category', 'tags.tag',
			'posts.value', 'posts.memo', 'posts.post_date', 'posts.cheer_flag'
		)->from('posts');

		$query->where("$where_key", "$post_id");

		$query->join('restaurants', 'INNER');
		$query->on('posts.post_rest_id', '=', 'restaurants.rest_id');

		$query->join('users', 'INNER');
		$query->on('posts.post_user_id', '=', 'users.user_id');

		$query->join('categories', 'LEFT OUTER');
		$query->on('posts.post_category_id', '=', 'categories.category_id');

		$query->join('tags', 'LEFT OUTER');
		$query->on('posts.post_tag_id', '=', 'tags.tag_id');

		$query->order_by('posts.post_date','desc');



		//配列[comments]に格納
		$post_data = $query->execute()->as_array();


		//--debug--//
		//echo "$data";

		return $post_data;
	}


}