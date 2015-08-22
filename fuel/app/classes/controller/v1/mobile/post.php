<?php
header('Content-Type: application/json; charset=UTF-8');
/*
* POST API
*　投稿に関するAPIです。
*　関数名の内容をPOSTします。
*/
class Controller_V1_Mobile_Post extends Controller_V1_Mobile_Base
{
	//SNS Link
	public function action_sns()
	{
		$keyword     = 'SNS連携';
		$user_id     = session::get('user_id');
		$provider    = Input::get('provider');
		$token       = Input::get('token');
		$profile_img = Input::get('profile_img');

		try
		{
			if ($profile_img != 'none')
			{
				$profile_img = Model_S3::input($user_id, $profile_img);
				Model_User::update_profile_img($user_id, $profile_img);
			}

			$identity_id = Model_User::get_identity_id($user_id);
			Model_User::update_sns_flag($user_id, $provider);
			Model_Cognito::post_sns($user_id, $identity_id, $provider, $token);

			$data = array(
				'code' 	      => 200,
				'message'     => "$keyword" . 'しました',
				'profile_img' => "$profile_img"
			);

			self::output_json($data);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}

	public function action_sns_unlink()
	{
		$keyword     = 'SNS連携解除';
		$user_id     = session::get('user_id');
		$provider    = Input::get('provider');
		$token       = Input::get('token');

		try
		{
			$identity_id = Model_User::get_identity_id($user_id);
			Model_User::delete_sns_flag($user_id, $provider);
			Model_Cognito::delete_sns($user_id, $identity_id, $provider, $token);

			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}


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

			if ($user_id != $target_user_id) {
				$record = Model_Notice::post_data(
					$keyword, $user_id, $target_user_id, $post_id);
			}

			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}


	//Comment
	public function action_comment()
	{
		$keyword = 'コメント';
		$user_id = session::get('user_id');
		$post_id = Input::get('post_id');
		$comment = Input::get('comment');

		try
		{
			$target_user_id = Model_Comment::post_comment(
				$user_id, $post_id, $comment);

			if ($user_id != $target_user_id) {
				$record = Model_Notice::post_data(
					$keyword, $user_id, $target_user_id, $post_id);
			}

			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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

			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}


	//Post
	public function action_post()
	{
		$keyword     = '投稿';
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
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}


	//プロフィール編集
	public function action_update_profile()
	{
		$keyword = 'プロフィールを変更';
		$user_id = session::get('user_id');
		$new_username    = Input::get('username');
		$new_profile_img = Input::get('profile_img');

		try
		{
			$user_data   = Model_User::get_profile($user_id);
			$username    = $user_data['username'];
			$profile_img = $user_data['profile_img'];


			if (!empty($new_username) && !empty($new_profile_img)) {
			//更新なし

			}elseif (!empty($new_profile_img)) {
			//プロフィール画像更新
				$profile_img = Model_User::update_profile_img($user_id, $new_profile_img);

			}elseif (!empty($new_username)) {
			//ユーザーネーム更新
				$username = Model_User::update_name($user_id, $new_username);

			}else{
			//両方更新
				$username = Model_User::update_profile($user_id, $new_username, $new_profile_img);
			}

			$data = array(
				'code' 	      => 200,
				'message'     => "$keyword" . 'しました',
				'username'    => "$username",
				'profile_img' => "$profile_img"
			);
			self::output_json($data);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}


	//Feedback
	public function action_feedback()
	{
		$keyword  = '意見を投稿';
		$user_id  = session::get('user_id');
		$feedback = Input::get('feedback');

		try
		{
			//$clean_feedback = Controller_V1_Inputfilter::action_encoding($feedback);
			$result = Model_Feedback::post_add($user_id, $feedback);
			self::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
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
			self::output_json($data);
		}

		catch(\Database_Exception $e)
		{
			self::failed($keyword);
			error_log($e);
		}
	}


	//DBデータ入力成功
	private static function success($keyword)
	{
		$data = array(
			'code' 	  => 200,
			'message' => "$keyword" . 'しました'
		);
		self::output_json($data);
	}


	//DBデータ入力エラー
	private static function failed($keyword)
	{
		$data = array(
			'code' 	  => 401,
			'message' => "$keyword" . 'できませんでした'
		);
		self::output_json($data);
	}
}