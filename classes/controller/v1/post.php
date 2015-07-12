<?php
header('Content-Type: application/json; charset=UTF-8');
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
			$target_user_id = Model_Gochi::post_gochi(
				$user_id, $post_id);

			$record = Model_Notice::post_data(
				$keyword, $user_id, $target_user_id, $post_id);

			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//Comment
	public function action_comment()
	{
		$user_id = session::get('user_id');
		$post_id = Input::get('post_id');
		$comment = Input::get('comment');

		$keyword = 'コメント';

		try
		{
			$target_user_id = Model_Comment::post_comment(
				$user_id, $post_id, $comment);

			$record = Model_Notice::post_data(
				$keyword, $user_id, $target_user_id, $post_id);

			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//Follow
	public function action_follow()
	{
		$keyword = 'フォロー';
		$user_id 		= session::get('user_id');
		$follow_user_id = Input::get('target_user_id');

		try
		{
			$result = Model_Follow::post_follow($user_id, $follow_user_id);

			$record = Model_Notice::post_data(
				$keyword, $user_id, $follow_user_id);

			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//Unfollow
	public function action_unfollow()
	{
		$keyword = 'フォローを解除';

		$user_id   		  = session::get('user_id');
		$unfollow_user_id = Input::get('target_user_id');

		try
		{
			$result = Model_Follow::post_unfollow($user_id, $unfollow_user_id);
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//Want
	public function action_want()
	{
		$keyword = '行きたい店リストに追加';

		$user_id = session::get('user_id');
		$rest_id = Input::get('rest_id');

		try
		{
			$result = Model_Want::post_want($user_id, $rest_id);
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//UnWant
	public function action_unwant()
	{
		$keyword = '行きたい店リストから解除';

		$user_id = session::get('user_id');
		$rest_id = Input::get('rest_id');

		try
		{
			$result = Model_Want::post_unwant($user_id, $rest_id);
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//Post
	public function action_post()
	{
		$keyword = '投稿';

		$user_id     = session::get('user_id');
		$rest_id     = Input::get('rest_id');
		$movie_name  = Input::get('movie_name');
		$category_id = Input::get('category_id');
		$tag_id      = Input::get('tag_id');
		$value       = Input::get('value');
		$memo        = Input::get('memo');
		$cheer_flag  = Input::get('cheer_flag');

		try
		{
			$result = Model_Post::post_data(
				$user_id, $rest_id, $movie_name,
				$category_id, $tag_id, $value, $memo, $cheer_flag);
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
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
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//PostDelete
	public function action_postdel()
	{
		$keyword = '投稿を消去';

		$post_id = Input::get('post_id');

		try
		{
			$result = Model_Post::post_delete($post_id);
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//プロフィール編集
	public function action_update_profile()
	{
		$keyword = 'プロフィールを変更';

		$user_id     = session::get('user_id');
		$username    = Input::get('username');
		$profile_img = Input::get('profile_img');

		try
		{
			if (empty($username + $profile_img)) {
				//更新なし

			}elseif (empty($username)) {
				$result = Model::update_profile_img($user_id, $profile_img);

			}elseif (empty($profile_img)) {
				$result = Model_User::update_name($user_id, $username);

			}else{
				$result = Model_User::update_profile(
					$user_id, $username, $profile_img);
			}

			$user_data   = Model_User::get_profile($user_id);
			$username    = $user_data['username'];
			$profile_img = $user_data['profile_img'];

			$data = array(
				'code' 	      => 200,
				'message'     => "$keyword" . 'しました',
				'username'    => "$username",
				'profile_img' => "$profile_img"
			);

			$status = $this->output_json($data);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//Feedback
	public function action_feedback()
	{
		$keyword = '意見を投稿';

		$user_id  = session::get('user_id');
		$feedback = Input::get('feedback');

		try
		{
			//$clean_feedback = Controller_V1_Inputfilter::action_encoding($feedback);
			$result = Model_Feedback::post_add($user_id, $feedback);
			$status = $this->success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
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
			$rest_id = Model_Restaurant::post_add($rest_name, $lat, $lon);

			$data = array(
				'code' 	  => 200,
				'message' => "$keyword" . 'しました',
				'rest_id' => "$rest_id"
			);

			$status = $this->output_json($data);
		}

		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}





	//----------------------------------------------------------------//


	private function publish($keyword, $user_id, $target_user_id)
	{
		try
		{
			$login_flag = Model_User::check_login($target_user_id);

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
        		curl_close($ch);
			}
		}
		catch(\Database_Exception $e)
		{
			$status = $this->failed($keyword);
			error_log($e);
		}
	}


	//DBデータ入力成功
	public function success($keyword)
	{
		$data = array(
			'code' 	  => 200,
			'message' => "$keyword" . 'しました'
		);

		$status = $this->output_json($data);
	}


	//DBデータ入力エラー
	public function failed($keyword)
	{
		$data = array(
			'code' 	  => 401,
			'message' => "$keyword" . 'できませんでした'
		);

		$status = $this->output_json($data);
	}

}