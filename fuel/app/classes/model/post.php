<?php
class Model_Post extends Model
{
	public static function get_data($post_id)
	{
		//クエリ文
		$query = DB::select(
			'posts.post_id', 'posts.post_user_id', 'users.username',
			'users.profile_img', 'users.cover_img', 'posts.post_rest_id',
			'restaurants.restname', 'posts.movie', 'posts.thumbnail',
			'posts.cheer_flag', 'posts.post_date', 'categories.category',
			'tags.tag', 'posts.value', 'posts.memo'
		)->from('posts');

		$query->where('posts.post_id', "$post_id");

		$query->join('restaurants', 'INNER');
		$query->on('posts.post_rest_id', '=', 'restaurants.rest_id');

		$query->join('users', 'INNER');
		$query->on('posts.post_user_id', '=', 'users.user_id');

		$query->join('categories', 'LEFT OUTER');
		$query->on('posts.post_category_id', '=', 'categories.category_id');

		$query->join('tags', 'LEFT OUTER');
		$query->on('posts.post_tag_id', '=', 'tags.tag_id');


		//配列[comments]に格納
		$result = $query->execute()->as_array();
		$data = array("post" => $result[0]);

		return $data;
	}

}