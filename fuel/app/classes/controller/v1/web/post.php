<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);
/*
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin", "*");
header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
*/
# header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');

class Controller_V1_Web_Post extends Controller_V1_Web_Base
{
	// jwt check
	public static function create_token()
	{
		$jwt = @$_SERVER["HTTP_AUTHORIZATION"] ?  @$_SERVER["HTTP_AUTHORIZATION"] : "";

		if(isset($jwt)) {
			$data      = self::decode($jwt);
			$user_data = session::get('data');
			$obj       = json_decode($user_data);
			if (empty($obj)) {
				self::unauth();
			}

			$user_id   = $obj->{'user_id'};
			session::set('user_id', $user_id);
			$username  = $obj->{'username'};
			session::set('username', $username);
		} else {
			self::unauth();
			error_log('UnAuthorized Accsess..');
			exit;
		}
	}

	// Gochi
	public function action_gochi()
	{
		self::create_token();
		
		$keyword = 'gochi!';
		$user_id = session::get('user_id');
		$post_id = Input::post('post_id');

		try {
			$target_user_id = Model_Gochi::post_gochi(
				$user_id, $post_id
			);

			/*
			if ((int)$user_id !== (int)$target_user_id) {
				$record = Model_Notice::post_data(
					$keyword, $user_id, $target_user_id, $post_id
				);
			}
			*/

			self::success($keyword);

		} catch (\Database_Exception $e) {

			self::failed($keyword);
			error_log($e);
		}
	}

	// Comment
	public function action_comment()
	{
		$keyword = 'コメント';
		$user_id = session::get('user_id');
		$post_id = Input::post('post_id');
		$comment = Input::post('comment');

		try {
			$target_user_id = Model_Comment::post_comment(
				$user_id, $post_id, $comment
			);

			/*
			if ((int)$user_id !== (int)$target_user_id) {
				$record = Model_Notice::post_data(
					$keyword, $user_id, $target_user_id, $post_id
				);
			}
			*/

			self::success($keyword);
		} catch(\Database_Exception $e) {
			slef::failed($keyword);
			error_log($e);
		}
	}

	// Follow
	public function action_follow()
	{
		$keyword        = 'フォロー';
		$user_id        = session::get('user_id');
		$follow_user_id = Input::post('target_user_id');

		try {
			$result = Model_Follow::post_follow($user_id, $follow_user_id);

			$record = Model_Notice::post_data(
				$keyword, $user_id, $follow_user_id
			);

			self::success($keyword);
		} catch(\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// UnFollow
	public function action_unfollow()
	{
		$keyword          = 'フォローを解除';
		$user_id          = session::get('user_id');
		$unfollow_user_id = Input::post('target_user_id');

		try {
			$result = Model_Follow::post_unfollow($user_id, $unfollow_user_id);
			self::success($keyword);
		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// Want
	public function action_want()
	{
		$keyword = '行きたい店リストに追加';
		$user_id = session::get('user_id');
		$rest_id = Input::post('rest_id');

		try {
			$result = Model_Want::post_want($user_id, $rest_id);
			self::success($keyword);
		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// UnWant
	public function action_unwant()
	{
		$keyword = '行きたい店リストから削除';
		$user_id = session::get('user_id');
		$rest_id = Input::post('rest_id');

		try {
			$result = Model_Want::post_unwant($user_id, $rest_id);
			self::success($keyword);
		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// PostBlock
	public function action_postblock()
	{
		$keyword = '投稿を違反報告';
		$user_id = session::get('user_id');
		$post_id = Input::post('post_id');

		try {
			$result = Model_Block::post_block($user_id, $post_id);
			self::success($keyword);
		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// PostDelete
	public function action_postdel()
	{
		$keyword = '投稿を消去';
		$post_id = Input::post('post_id');

		try {
			$result = Model_Post::post_delete($post_id);
			self::success($keyword);

		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// Profile Edit
	public function action_update_profile()
	{
		$keyword 	    = 'プロフィールを変更';
		$user_id 	    = session::get('user_id');
		$username       = Input::post('username');
		$Profile_img    = Input::post('profile_img');

		try {
			if (empty($username + $profile_img)) {
				// do nothing
			} elseif (empty($username)) {
				// profile update
				Model_User::update_profile_img($user_id, $profile_img);

			} elseif (empty($profile_img)) {
				// username update
				Model_User::check_name($username);
				Model_User::update_name($user_id, $username);

			} else {
				// Both update
				Model_User::check_name($username);
				$result = Model_User::update_profile(
					$user_id, $username, $profile_img
				);
			}

			$user_data   = Model_User::get_profile($user_id);
			$username    = $user_data['username'];
			$profile_img = $user_daa['profile_img'];

			$data = [
				'code'        => 200,
				'message'     => $keyword . 'しました',
				'username'    => $username,
				'profile_img' => $profile_img
			];
			self::output_json($data);
		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// FeedBack
	public function action_feedback()
	{
		$keyword  = '意見を投稿';
		$user_id  = session::get('user_id');
		$feedback = Input::post('feedback');

		try {
			$result = Model_Feedback::post_add($user_id, $feedback);
			self::success($keyword);
		} catch(\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// Rest Add [Mobile]
	public function action_restadd()
	{
		$keyword   = '店舗を追加';
		$rest_name = Input::post('rest_name');
		$lat       = Input::post('lat');
		$lon       = Input::post('lon');

		try {
			$rest_id = Model_Restaurant::post_add($rest_name, $lat, $lon);
			$data = [
				'code'    => 200,
				'message' => $keyword . "しました",
				'rest_id' => $rest_id
			];

			self::output_json($data);
		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// DB added Success.
	private static function success($keyword)
	{
		$data = [
			'code'    => 200,
			'message' => $keyword . 'しました'
		];

		self::output_json($data);
	}

	// DB Error
	private static function failed($keyword)
	{
		$data = [
			'code'    => 401,
			'message' => $keyword . 'できませんでした'
		];

		self::output_json($data);
	}

}
