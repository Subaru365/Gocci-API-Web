<?php

class Controller_V1_Post extends Controller
{

	public function cast($key, $result)
	{
		if($result){
			$result = array(
				'code' 	  => 200,
				'message' => "$key" . 'しました。'
			);
		}else{
			$result = array(
				'code'	  => 401,
				'message' => "$key" . 'できませんでした。'
			);
			error_log('Controller_Post_gochi: '
				. "$post_id" . ' / ' . "$user_id" . "$key" .'に失敗');
		}

		$status = json_encode(
			$result,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);

		return $status;
	}




	public function action_gochi()
	{
		$user_id 	   = Input::get('user_id');
		$gochi_post_id = Input::get('post_id');

		$result = Model_Like::post_gochi($user_id, $post_id);

		//$status = Response::forge($result);

		$key = 'gochi!';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";
	}


	public function action_follow()
	{
		$user_id 		= Input::get('user_id');
		$follow_user_id = Input::get('target_user_id');

		$result = Model_Follow::post_follow($user_id, $follow_user_id);

		$key = 'フォロー';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_unfollow()
	{
		$user_id   		  = Input::get('user_id');
		$unfollow_user_id = Input::get('target_user_id');

		$result = Model_Follow::post_unfollow($user_id, $unfollow_user_id);

		$key = 'フォローを解除';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_want()
	{
		$user_id 		= Input::get('user_id');
		$want_rest_id   = Input::get('rest_id');

		$result = Model_Want::post_want($user_id, $want_rest_id);

		$key = '行きたい店リストに追加';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_unwant()
	{
		$user_id 		= Input::get('user_id');
		$unwant_rest_id = Input::get('rest_id');

		$result = Model_Want::post_unwant($user_id, $unwant_rest_id);

		$key = '行きたい店リストから解除';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_postblock()
	{
		$user_id	   = Input::get('user_id');
		$block_post_id = Input::get('post_id');

		$result = Model_Block::post_block($user_id, $block_post_id);

		$key = '投稿を違反報告';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_restadd()
	{
		$user_id	   = Input::get('user_id');
		$add_rest_name = Input::get('rest_name');
		$add_lat 	   = Input::get('lat');
		$add_lon	   = Input::get('lon');

		$result = Model_Rest::post_add(
			$user_id, $add_rest_name, $add_lat, $add_lon);

		$key = '店舗を追加';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}

	public function action_postdel()
	{
		$user_id   	    = Input::get('user_id');
		$delete_post_id = Input::get('post_id');

		$result = Model_Post::post_delete($user_id, $delete_post_id);

		$key = '投稿を消去';

		$status = Controller_V1_Post::cast($key, $result);

	   	echo "$status";

	}





}