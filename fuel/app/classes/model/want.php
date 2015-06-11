<?php
class Model_Want extends Model
{
	public static function get_flag($user_id, $post_rest_id)
	{
		//クエリ文
		$query = DB::select('want_id')->from('wants');
		$query->where 	  ('want_user_id', "$user_id");
		$query->and_where ('want_rest_id', "$post_rest_id");


		$result = $query->execute()->as_array();

		if ($result == true) {
			$want_flag = 1;
		}else{
			$want_flag = 0;
		}


		//--debug--//
		//echo "$follow_flag";

		return $want_flag;
	}
}