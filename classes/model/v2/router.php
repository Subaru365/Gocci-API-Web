<?php
/**
* Controllerからの処理をここに集合させ、ハブとなるクラスです
* Aws, DBの処理は必ずこのモデルを通過します
*/
class Model_V2_Router extends Model
{
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


	//---------------------------------------------------------------------------------//


	public static function get_post_data($option)
	{
		$query 		= Model_V2_Db_Post::get_data();

		//$sort_keyによる絞り込み

		}elseif ($sort_key == 'post') {
			$query->where('post_id', $sort_id);

		}elseif ($sort_key == 'rest') {
			$query->where('post_rest_id', $sort_id);

		}elseif ($sort_key == 'user') {
			$query->where('user_id', $sort_id);

		}elseif ($sort_key == 'users') {
			$query->where('user_id', 'in', $sort_id);

		}else{
			error_log("Model_Post:${sort_key}が不正です");
			exit;
		}

		$post_data  = Model_V2_Db_Post::get_sort($query, $option);



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
	    	$post_data[$i]['follow_flag']   = Model_V2_Db_Follow::get_flag($post_data[$i]['user_id'];);
	    	$post_data[$i]['gochi_flag']    = Model_V2_Db_Gochi::get_flag($post_data[$i]['post_id']);
			$post_data[$i]['post_date']     = Model_V2_Date::get_data($post_data[$i]['post_date']);
		}

		return $post_data;
	}


	private static function add_post_data($post_data)
	{
		//付加情報格納(like_num, comment_num, want_flag, follow_flag, like_flag)

		$post_num  = count($post_data);

		for ($i=0; $i < $post_num; $i++) {

			$post_data[$i]['mp4_movie']		= Model_V2_Transcode::decode_mp4_movie($post_data[$i]['movie']);
			$post_data[$i]['hls_movie']     = Model_V2_Transcode::decode_hls_movie($post_data[$i]['movie']);
			$post_data[$i]['thumbnail']     = Model_V2_Transcode::decode_thumbnail($post_data[$i]['thumbnail']);
			$post_data[$i]['profile_img']   = Model_V2_Transcode::decode_profile_img($post_data[$i]['profile_img']);

	   		$post_data[$i]['gochi_num']		= Model_V2_Db_Gochi::get_num($post_data[$i]['post_id']);
	   		$post_data[$i]['comment_num']   = Model_V2_Db_Comment::get_num($post_data[$i]['post_id']);
	    	$post_data[$i]['want_flag']	    = Model_V2_Db_Want::get_flag($post_data[$i]['rest_id']);
	    	$post_data[$i]['follow_flag']   = Model_V2_Db_Follow::get_flag($post_data[$i]['user_id'];);
	    	$post_data[$i]['gochi_flag']    = Model_V2_Db_Gochi::get_flag($post_data[$i]['post_id']);
			$post_data[$i]['post_date']     = Model_V2_Date::get_data($post_data[$i]['post_date']);
		}

		return $post_data;
	}

}