<?php
/**
* Controllerからの処理をここに集合させ、ハブとなるクラスです
* Aws, DBの処理は必ずこのモデルを通過します
*/
class Model_V2_Router extends Model
{
	//===============================================================//
	//Auth

	public static function create_user($user_data)
	{
		$user_data['user_id'] = Model_V2_Db_User::get_user_id_next();

		session::set('user_id', $user_data['user_id']);

		$result						= Model_V2_Aws_Cognito::set_data();
		$user_data['identity_id']   = $result['IdentityId'];
		$user_data['token']	        = $result['token'];

		$user_data['profile_img']   = Model_V2_Db_User::set_data($user_data);
		$user_data['endpoint_arn']  = Model_V2_Aws_Sns::set_endpoint($user_data);

		Model_Device::set_data($user_data);

		return $user_data;
	}


	public static function login($identity_id)
	{
        $user_data = Model_V2_Db_User::get_auth($identity_id);

        session::set('user_id', $user_data['user_id']);

        $user_data['token'] = Model_V2_Aws_Cognito::get_token($identity_id);

        Model_V2_Db_Login::post_login();

        return $user_data;
	}


	public static function pass_login($username)
	{
		$identity_id 	= Model_V2_Db_User::get_identity_id($username);
		$user_data		= self::login($identity_id);

		return $user_data;
	}

	//=================================================================//
	//Post

	public static function timeline($option)
	{
		$query 		= Model_V2_Db_Post::get_data();

		$post_data  = Model_V2_Db_Post::get_sort($query, $option);

		$post_data  = self::add_post_data($post_data);
		return $post_data;
	}


	public static function followline($option)
	{
		$query 		= Model_V2_Db_Post::get_data();

		$follow_user_id = Model_Follow::get_follow_id();
		$query->where('user_id', 'in', $follow_user_id);

		$post_data  = Model_V2_Db_Post::get_sort($query, $option);
		$post_data  = self::add_post_data($post_data);

		return $post_data;
	}


	public static function comment_post($post_id)
	{
		$query 		= Model_V2_Db_Post::get_data();
		$query      ->where('post_id', $post_id);

		$post_data  = $query->execute()->as_array();
		$post_data  = self::add_post_data($post_data);

		return $post_data[0];
	}


	public static function rest_post($rest_id)
	{
		$query 		= Model_V2_Db_Post::get_data();
		$query      ->where('rest_id', $rest_id);

		$post_data  = $query->execute()->as_array();
		$post_data  = self::add_post_data($post_data);

		return $post_data;
	}


	public static function user_post($option)
	{
		$query 		= Model_V2_Db_Post::get_data();
		$query      ->where('user_id', $sort_id);

		$post_data  = $query->execute()->as_array();
		$post_data  = self::add_post_data($post_data);

		return $post_data;
	}

	//-----------------------------------------------------------------//

	private static function add_post_data($post_data)
	{
		$post_num  = count($post_data);

		for ($i=0; $i < $post_num; $i++) {

			$post_data[$i]['mp4_movie']		= Model_V2_Transcode::decode_mp4_movie($post_data[$i]['movie']);
			$post_data[$i]['hls_movie']     = Model_V2_Transcode::decode_hls_movie($post_data[$i]['movie']);
			$post_data[$i]['thumbnail']     = Model_V2_Transcode::decode_thumbnail($post_data[$i]['thumbnail']);
			$post_data[$i]['profile_img']   = Model_V2_Transcode::decode_profile_img($post_data[$i]['profile_img']);

			//付加情報格納(like_num, comment_num, want_flag, follow_flag, like_flag)
			$post_data[$i]['gochi_num']		= Model_V2_Db_Gochi::get_num($post_data[$i]['post_id']);
			$post_data[$i]['comment_num']   = Model_V2_Db_Comment::get_num($post_data[$i]['post_id']);
			$post_data[$i]['want_flag']	    = Model_V2_Db_Want::get_flag($post_data[$i]['rest_id']);
			$post_data[$i]['follow_flag']   = Model_V2_Db_Follow::get_flag($post_data[$i]['user_id']);
			$post_data[$i]['gochi_flag']    = Model_V2_Db_Gochi::get_flag($post_data[$i]['post_id']);
			$post_data[$i]['post_date']     = Model_V2_Date::get_data($post_data[$i]['post_date']);
		}

		return $post_data;
	}


	//=================================================================//
	//Other

	public static function comment($post_id)
	{
		$memo_data		= Model_V2_Db_Post::get_memo($post_id);
		$comment_data   = Model_V2_Db_Comment::get_data($post_id);

		//投稿者のmemoを$comment_dataに格納
		array_unshift($comment_data, $post_memo);

		$comment_num 	= count($comment_data);

		for ($i=1; $i < $comment_num; $i++) {
			$comment_data[$i]['re_user']	= Model_Re::get_data($comment_data[$i]['comment_id']);
		}

		return $comment_data;
	}


	public static function rest($rest_id)
	{
		$rest_data = Model_V2_Db_Restaurant::get_data($rest_id);

		$rest_data['want_flag']		= Model_Want::get_flag($rest_id);
		$rest_data['cheer_num']     = Model_Post::get_rest_cheer_num($rest_id);

		return $rest_data;
	}


	public static function user($target_user_id)
	{
		$user_data	= Model_V2_Db_User::get_data($target_user_id);

        $user_data['follow_num']	= Model_Follow::follow_num($target_user_id);
        $user_data['follower_num']  = Model_Follow::follower_num($target_user_id);
        $user_data['cheer_num']     = Model_Post::get_user_cheer_num($target_user_id);
        $user_data['want_num']      = Model_Want::want_num($target_user_id);
        $user_data['follow_flag']   = Model_Follow::get_flag($target_user_id);

        return $user_data;
	}


	public static function notice()
	{
    	$notice_data = Model_Notice::get_data();
	   	Model_User::reset_badge();
	   	return $notice_data;
	}

	public static function follow_list($user_id)
	{
		$follow_data = Model_Follow::get_follow($user_id);
		return $follow_data;
	}

	public static function follower_list($user_id)
	{
		$follower_data = Model_Follow::get_follower($user_id);
		return $follower_data;
	}

	public static function want_list($user_id)
	{
		$want_data		= Model_Want::get_want($user_id);
		return $want_data;
	}


	//=================================================================//
	//Function

	private static function data_conversion($data)
	{
		$num = count($data);

		for ($i=0; $i < $num; $i++) {

			$data[$i]['profile_img'] =
				Model_Transcode::decode_profile_img($data[$i]['profile_img']);

			$data[$i]['comment_date'] =
				Model_Date::get_data($data[$i]['comment_date']);
		}

		return $data;
	}

}