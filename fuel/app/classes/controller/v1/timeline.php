<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * timeline api
 *
 */

class Controller_V1_Timeline extends Controller
{

    public function action_index()
    {

        $user_id = Input::get('user_id');
        $limit 	 = Input::get('limit');

		if (empty($limit)) {
		    $limit = 10;
		}


		if (!empty($user_id)) {

			//--------------------------------------------//
			//"POST_Data"
			//--------------------------------------------//

			$sort_key  = 'all';
			$post_data = Model_Post::get_data($sort_key, $sort_key, $limit);


			for ($i=0; $i < $limit; $i++) {

				$post_id	  = $post_data[$i]['post_id'];
				$post_user_id = $post_data[$i]['post_user_id'];
				$post_rest_id = $post_data[$i]['post_rest_id'];
				$post_date    = $post_data[$i]['post_date'];

	    		$like_num 	  = Model_Like::get_num($post_id);
	    		$post_data[$i]['like_num']    = $like_num;

	    		$comment_num  = Model_Comment::get_num($post_id);
	    		$post_data[$i]['comment_num'] = $comment_num;

	    		$want_flag	  = Model_Want::get_flag($user_id, $post_rest_id);
	    		$post_data[$i]['want_flag']	  = $want_flag;

	    		$follow_flag  = Model_Follow::get_flag($user_id, $post_user_id);
	    		$post_data[$i]['follow_flag'] = $follow_flag;

	    		$like_flag	  = Model_Like::get_flag($user_id, $post_id);
	    		$post_data[$i]['like_flag']   = $like_flag;


	    		$datetime1 = new DateTime("$post_date");
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

				$post_data[$i]['post_date'] = $date_diff;

			}

	    	$timelinepage = json_encode($post_data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	    	echo "$timelinepage";

	    }
	}
}

