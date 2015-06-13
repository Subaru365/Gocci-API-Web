<?php
class Model_Comment extends Model
{

	public static function get_data($post_id)
	{

		//クエリ文

		$query = DB::select(
			'comments.comment_user_id', 'users.username', 'users.profile_img',
			'comments.comment', 'comments.comment_date'
		)->from('comments');

		$query->where('comments.comment_post_id', "$post_id");

		$query->join('users', 'INNER');
		$query->on('comments.comment_user_id', '=', 'users.user_id');


		//格納

		$comment_data = $query->execute()->as_array();
		$comment_num  = count($comment_data);


		for ($i=0; $i < $comment_num; $i++) {

			//comment_date表示形式 変換

			$comment_date = $comment_data[$i]['comment_date'];

	    	$datetime1 = new DateTime("$comment_date");
			$datetime2 = new DateTime(date('Y-m-d H:i:s'));

			$interval = $datetime1->diff($datetime2);

			if ($interval->format('%y') > 0) {
				$date_diff = $interval->format('%y') . '年前';

			}elseif ($interval->format('%m') > 0) {
				$date_diff = $interval->format('%m') . 'ヶ月前';

			}elseif ($interval->format('%d') > 0) {
				$date_diff = $interval->format('%d') . '日前';

			}elseif ($interval->format('%h') > 0) {
				$date_diff = $interval->format('%h') . '時間前';

			}elseif ($interval->format('%i') > 0) {
				$date_diff = $interval->format('%i') . '分前';

			}elseif ($interval->format('%s') > 0) {
				$date_diff = $interval->format('%s') . '秒前';

			}else{
				$date_diff = '未来から';
				error_log('$post_dateの時刻エラー');
			}

			$comment_data[$i]['comment_date'] = $date_diff;

		}

		//--debug--//
		//echo "$comment_data";

		return $comment_data;
	}



	public static function get_num($post_id)
	{
		//クエリ文
		$query = DB::select('comment_id')->from('comments');
		$query->where('comment_post_id', "$post_id");


		$result = $query->execute()->as_array();
	   	$comment_num = count($result);


	   	//--debug--//
	   	//echo "$comment_num";

		return $comment_num;
	}

}