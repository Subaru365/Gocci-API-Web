<?php
class Model_Date extends Model

{

	public static function get_data($date)
	//TIMESTAMPから現在までの差分を求める
	{

		$datetime1 = new DateTime("$date");
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

		return $date_diff;

	}

}