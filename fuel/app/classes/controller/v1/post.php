<?php

class Controller_V1_Post extends Controller
{

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



	public function action_gochi()
	{
		$user_id = Input::get('user_id');
		$post_id = Input::get('post_id');

		$keyword = 'gochi!';

		try
		{
			$result = Model_Like::post_gochi($user_id, $post_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";
	}


	public function action_follow()
	{
		$user_id 		= Input::get('user_id');
		$follow_user_id = Input::get('target_user_id');

		$keyword = 'フォロー';

		try
		{
			$result = Model_Follow::post_follow($user_id, $follow_user_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

	   	echo "$status";

	}

	public function action_unfollow()
	{
		$user_id   		  = Input::get('user_id');
		$unfollow_user_id = Input::get('target_user_id');

		$keyword = 'フォローを解除';

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

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_want()
	{
		$user_id = Input::get('user_id');
		$rest_id = Input::get('rest_id');

		$keyword = '行きたい店リストに追加';

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

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_unwant()
	{
		$user_id = Input::get('user_id');
		$rest_id = Input::get('rest_id');

		$keyword = '行きたい店リストから解除';

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

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_postblock()
	{
		$user_id = Input::get('user_id');
		$post_id = Input::get('post_id');

		$keyword = '投稿を違反報告';

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

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_restadd()
	{
		$rest_name = Input::get('rest_name');
		$lat 	   = Input::get('lat');
		$lon	   = Input::get('lon');

		$keyword = '店舗を追加';

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

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_postdel()
	{
		$user_id = Input::get('user_id');
		$post_id = Input::get('post_id');

		$keyword = '投稿を消去';

		try
		{
			$result = Model_Post::post_delete($user_id, $post_id);
			$status = Controller_V1_Post::success($keyword);
		}

		catch(\Database_Exception $e)
		{
			$status = Controller_V1_Post::failed($keyword);
			error_log($e);
		}

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}





}