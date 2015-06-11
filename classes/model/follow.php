<?php
class Model_Follow extends Model
{
	public static function get_flag($user_id, $post_user_id)
	{
		//クエリ文
		$query = DB::select('follow_id')->from('follows');
		$query->where 	  ('follow_a_user_id', "$user_id");
		$query->and_where ('follow_p_user_id', "$post_user_id")


		$result = $query->execute()->as_array();

		if ($result == true) {
			$follow_flag = 1;
		}else{
			$follow_flag = 0;
		}

		return $follow_flag;
	}
}