<?php
class Model_Notice extends Model_Crud
{
     protected static $_table_name = 'notices';

     public static function get_data($user_id)
     {
     	$query = DB::select(
			'notices.notice_id', 'notices.notice_a_user_id', 'users.username',
			'users.profile_img', 'notices.notice', 'notices.notice_post_id',
			'notices.read_flag', 'notices.notice_date')

     	->from('notices')

		->join('users', 'INNER')
		->on('notices.notice_a_user_id', '=', 'users.user_id')

		->order_by('notices.notice_date','desc')

		->limit('15')

		->where('notices.notice_p_user_id', "$user_id");

		$notice_data = $query->execute()->as_array();

		return $notice_data;
     }
}
