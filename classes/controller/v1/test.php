<?php
error_reporting(-1);

class Controller_V1_Test extends Controller
{

	public function action_index()
	{
		$limit = 30;
		$sort_key  = 'all';
		$post_data = Model_Post::get_data($sort_key, $sort_key, $limit);
		$post_num  = count($post_data);


		for ($i=0; $i < $post_num; $i++) {
			$post_date[$i] = $post_data[$i]['post_date'];

			$datetime1 = new DateTime("$post_date[$i]");
			$datetime2 = new DateTime(date('Y-m-d H:i:s'));



			$interval = $datetime1->diff($datetime2);


			print_r("$post_date[$i]" . "\n");
			echo $interval->format('%y, %m, %d, %h, %i, %s');

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

			echo "\n" . "$date_diff" . "\n";

		}





	}
}