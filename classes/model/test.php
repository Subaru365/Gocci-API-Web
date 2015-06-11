<?php
class Model_Test extends Model
{
	public static function get_data($sort_key, $post_id)
	{
		if ($sort_key == 'post') {
			$where_key = 'posts.post_id';

		}elseif ($sort_key == 'rest') {
			$where_key = 'posts.post_rest_id';

		}else{
			$where_key = '$sort_keyが間違っています！';
		}

		return $where_key;
	}

}