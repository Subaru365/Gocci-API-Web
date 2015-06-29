<?php
/*
* POST API
*　投稿に関するAPIです。
*　関数名の内容をPOSTします。
*/

class Controller_V1_Post extends Controller_V1_Base
{
	//Gochi!
	public function action_gochi()
	{
		$keyword = 'gochi!';

		$user_id = session::get('user_id');
		$post_id = Input::get('post_id');


		try
		{
			$target_user_id = Model_Like::post_gochi(
				$user_id, $post_id);

			$record = Model_Notice::post_data(
				$keyword, $user_id, $target_user_id, $post_id);

			$sns_push = $this->publish(
				$keyword, $user_id, $target_user_id, $post_id);

			$status   = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	   	curl_close($ch);
	}


	//Comment
	public function action_comment()
	{
		$user_id = Input::get('user_id');
		$post_id = Input::get('post_id');
		$comment = Input::get('comment');

		$keyword = 'コメント';

		try
		{
			$target_user_id = Model_Comment::post_comment(
				$user_id, $post_id, $comment);

			$record = Model_Notice::post_data(
				$keyword, $user_id, $target_user_id, $post_id);

			$sns_push = $this->publish(
				$keyword, $user_id, $target_user_id, $post_id);

			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	   	curl_close($ch);
	}


	//Follow
	public function action_follow()
	{
		$keyword = 'フォロー';
		$user_id 		= Input::get('user_id');
		$follow_user_id = Input::get('target_user_id');

		try
		{
			$result = Model_Follow::post_follow($user_id, $follow_user_id);

			$sns_push = $this->publish(
				$keyword, $user_id, $target_user_id, $post_id);

			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	   	curl_close($ch);
	}


	//Unfollow
	public function action_unfollow()
	{
		$keyword = 'フォローを解除';

		$user_id   		  = Input::get('user_id');
		$unfollow_user_id = Input::get('target_user_id');

		try
		{
			$result = Model_Follow::post_unfollow($user_id, $unfollow_user_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	//Want
	public function action_want()
	{
		$keyword = '行きたい店リストに追加';

		$user_id = Input::get('user_id');
		$rest_id = Input::get('rest_id');

		try
		{
			$result = Model_Want::post_want($user_id, $rest_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	//UnWant
	public function action_unwant()
	{
		$keyword = '行きたい店リストから解除';

		$user_id = Input::get('user_id');
		$rest_id = Input::get('rest_id');

		try
		{
			$result = Model_Want::post_unwant($user_id, $rest_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	//PostBlock
	public function action_postblock()
	{
		$keyword = '投稿を違反報告';

		$user_id = session::get('user_id');
		$post_id = Input::get('post_id');

		try
		{
			$result = Model_Block::post_block($user_id, $post_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	//RestAdd
	public function action_restadd()
	{
		$keyword = '店舗を追加';

		$rest_name = Input::get('rest_name');
		$lat 	   = Input::get('lat');
		$lon	   = Input::get('lon');

		try
		{
			$result = Model_Restaurant::post_add($rest_name, $lat, $lon);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	//PostDelete
	public function action_postdel()
	{
		$keyword = '投稿を消去';

		$post_id = Input::get('post_id');

		try
		{
			$result = Model_Post::post_delete($post_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	//----------------------------------------------------------------//


	private function publish($keyword, $user_id, $target_user_id)
	{
		try
		{
			$login_flag = Model_User::get_login($target_user_id);

			if ($check_login == '1') {
				//ログイン中

				//SNS Push 外部処理
        		$ch = curl_init();

        		curl_setopt($ch, CURLOPT_URL,
            		'http://localhost/v1/background/sns/push/?' .
                		'keyword='   . "$keyword"         . '&' .
                		'a_user_id=' . "$username"        . '&' .
                		'p_user_id=' . "$target_user_id"
        		);

        		curl_exec($ch);
        		//curl_close($ch);

			}else{
				//ログアウト中
			}
		}
	}


	//DBデータ入力成功関数
	public function success($keyword)
	{
		$result = array(
			'code' 	  => 200,
			'message' => "$keyword" . 'しました。'
		);

		$status = json_encode(
			$result,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);

		return $status;
	}


	//DBデータ入力エラー関数
	public function failed($keyword)
	{
		$result = array(
			'code' 	  => 401,
			'message' => "$keyword" . 'できませんでした。'
		);

		$status = json_encode(
			$result,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);

		return $status;
	}


}