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
		        $exp       = $obj->{'exp'};
		        session::set('exp', $exp);
		} else {
			self::unauth();
			error_log('UnAuthorized Accsess..');
			exit;
		}
	}

	// SNS連携
	public function action_sns()
        {
		self::create_token();
		$keyword     = 'SNS連携'; 
		$user_id     = session::get('user_id');
		$provider    = Input::post('provider');
		$token       = Input::post('token');
		$profile_img = Input::post('profile_img');

	        try {
		   
		    if ($profile_img !== 'none') {
		    	$profile_img = Model_S3::input($user_id, $profile_img);
		    	$profile_img = Modle_User::update_profile_img($user_id, $profile_img);
		    }
		    $identity_id = Model_User::get_identity_id($user_id);
		    Model_User::update_sns_flag($user_id, $provider);
		    Model_Cognito::post_sns($user_id, $identity_id, $provider, $token);

		    $data = [
			"api_version" => 3,
			"api_code" => 0,
			"login_flag" => 1, // 連携するということは、ログインしており設定ページからなのでログインしているから1。
			"api_message" => $keyword . "しました",
			"api_data" => [
			    "profile_img" => $profile_img
			]
		    ];
	
		    // JSON出力
  	            self::output_json($data);

		} catch (\Database_Exception $e) {
		    self::failed($keyword);
		    error_log($e);
		}
        }

	// sns解除
	public function action_unlink()
        {
	        self::create_token();
		exit;
		$keyword = 'SNS連携解除';
		$user_id = Input::post('provider');
		$provider = Input::post('provider');
		$token  = Input::post('token');

		try {
			$identity_id = Model_User::get_indentity_id($user_id);
			Model_User::deleet_sns_flag($user_id, $provider);
			Model_Cognito::delete_sns($user_id, $identity_id, $provider, $token);

			self::success($keyword);

		} catch (\Dataase_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
        }

	// Gochi => Like
	public function action_gochi()
	{
		self::create_token();
		
		$keyword = 'gochi';
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
	        self::create_token();
		$keyword = 'コメント';
		$user_id = session::get('user_id');
	        error_log('user_idの中身');
		error_log($user_id);
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
			self::failed($keyword);
			error_log($e);
		}
	}

	// Follow
	public function action_follow()
	{
		self::create_token();
		$keyword        = 'フォロー';
		$user_id        = session::get('user_id');
	        // $user_id = 4;
		$follow_user_id = Input::post('target_user_id');

		try {
			$result = Model_Follow::post_follow($user_id, $follow_user_id);

			$record = Model_Notice::web_notice_insert(
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
		 self::create_token();
		$keyword          = 'フォローを解除';
		$user_id          = session::get('user_id');
		$unfollow_user_id = Input::post('target_user_id');

		try {
			$result = Model_Follow::post_unfollow($user_id, $unfollow_user_id);
	
			$record = Model_Notice::web_notice_insert(
                                $keyword, $user_id, $follow_user_id
                        );
			self::success($keyword);


		} catch (\Database_Exception $e) {
			self::failed($keyword);
			error_log($e);
		}
	}

	// Want
	public function action_want()
	{
		self::create_token();
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
		self::create_token();
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
		self::create_token();
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
		self::create_token();
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
		self::create_token();
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
		self::create_token();
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
		self::create_token();
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

	// Password変更 api
        public function action_password_change()
        {
            // ログインしているユーザIDを取得
	    self::create_token();
	    $user_id = session::get('user_id');
	    // $user_id = 503;
            // POST: ユーザーの現在のパスワード
            $current_password = Input::post('current_password');
            // POST: 変更したい新しいパスワード
            $new_password     = Input::post('new_password');
	    try {
                // ユーザのIDから登録時のパスワードを取得し、送信されたパスワードとDBパスワード一致するか調べる
	        $db_password = Model_User::get_current_db_pass($user_id, $current_password);
		// current_passwordはhash化毎に変わるので、以下だと絶対一致しない
		$match_pass = Model_User::web_verify_pass($current_password, $db_password[0]['password']);
		
		if ($match_pass) {
		    // 一致
		    Model_User::update_pass($user_id, $new_password);
		
		    // 正式に変更できたら、jsonでパスワードを変更しましたを含むJSONを吐く
		    $data = [
		        "message" => "パスワードを変更しました"
		    ];
		    $base_data = [
            		    "api_version" => 3,
            		    "api_code"    => 0,
           		    "api_message" => "success",
            		    "api_data" => $data
       		    ];

		    $status         = $this->output_json($base_data);
		}  else {
		    // 送ってもらったパスワードが登録されたパスワードと違う
		    error_log('パスワードが一致しません');
                    Controller_V1_Web_Base::error_json("パスワードが正しくありません");
                    exit;
		}
	    } catch (\Database_Exception $e) {
		

	    }

        }

}
