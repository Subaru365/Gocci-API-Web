<?php
/**
 * Post Class Api
 * @package    Gocci-Web
 * @version    3.1 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);

class Controller_V1_Web_Post extends Controller_V1_Web_Base
{
    /**
     * jwt check
     * 
     * @param string POST $uri   
     * @param string POST $login_flag
     */

    public static function create_token($uri="", $login_flag)
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

    /**
     * SNS連携
     *
     */
    public function action_sns()
    {
        self::create_token($uri="/get/timeline", $login_flag=0);
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
                "api_version" => 3.1,
                "api_uri"     => "/post/sns",
                "api_code"    => 0,
                "login_flag"  => 1,
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

    /**
     * sns解除
     *
     */
    public function action_unlink()
    {
           self::create_token($uri="/post/unlink", $login_flag=1);
           $keyword  = 'SNS連携解除';
           $user_id  = session::get('user_id');
           $provider = Input::post('provider');
           $token    = Input::post('token');

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

	/**
         * Gochi->Like
         *
         * @param string POST $uri
         * @param string POST $login_flag
         */
        public function action_gochi()
        {
            self::create_token($uri="/post/gochi", $login_flag=0);      
            $keyword = 'gochi';
            $user_id = session::get('user_id');
            $post_id = Input::post('post_id');

            try {
                // いいねしたことをテーブルに格納
                $target_user_id = Model_Gochi::post_gochi(
                    $user_id, $post_id
                );

                if ((int)$user_id !== (int)$target_user_id) {
                    // noticeテーブルにインサート and 通知
                    $record = Model_Notice::notice_insert(
                        $keyword, $user_id, $target_user_id, $post_id
                    );
                }
                        
                self::success($keyword);
            } catch (\Database_Exception $e) {
                self::failed($keyword);
                error_log($e);
            }
        }

        /**
         * comment
         *
         * 
         */
        public function action_comment()
        {
            self::create_token($uri="/post/comment", $login_flag=0);
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
                
                if ((int)$user_id !== (int)$target_user_id) {
                    $record = Model_Notice::notice_insert(
                        $keyword, $user_id, $target_user_id, $post_id
                    );
                }
               
                self::success($keyword);
            } catch(\Database_Exception $e) {
                self::failed($keyword);
                error_log($e);
            }
        }

	/**
     	* Follow
    	*
     	* @param string POST $uri
     	* @param string POST $login_flag
     	*/
        public function action_follow()
        {
            self::create_token($uri="/post/follow", $login_flag=0);
            $keyword        = 'フォロー';
            $user_id        = session::get('user_id');
            $follow_user_id = Input::post('target_user_id');

            try {
                $result = Model_Follow::post_follow($user_id, $follow_user_id);
                $record = Model_Notice::notice_insert(
                    $keyword, $user_id, $follow_user_id
                );

                self::success($keyword);
            } catch(\Database_Exception $e) {
                self::failed($keyword);
                error_log($e);
            }
        }

	/**
     	* UnFollow
    	*
     	*/
        public function action_unfollow()
        {
            self::create_token($uri="post/unfollow", $login_flag=0);
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

	/**
     	* Want
     	*
    	* @return string
     	*/
        public function action_want()
        {
            self::create_token($uri="/post/want", $login_flag=0);
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

	/**
     	* UnWant
     	*
     	* @return string
     	*/
        public function action_unwant()
        {
            self::create_token($uri="/post/unwant", $login_flag=0);
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

	/**
     	* PostBlock
     	*
     	* @return string
     	*/
        public function action_postblock()
        {
            self::create_token($uri="/post/postblock", $login_flag=0);
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

	/**
     	* PostDelete
    	*
     	* @return string
     	*/
        public function action_postdel()
        {
            self::create_token($uri="/post/postdel", $login_flag=0);
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

	/**
     	* Profile Edit
     	*
     	* @return string
     	*/
        public function action_update_profile()
        {
            self::create_token($uri="post/update_profile", $login_flag=0);
            // error_log('update_profile 0');
            $keyword        = 'プロフィールを変更';
            $user_id        = session::get('user_id');
            $username       = Input::post('username');
            error_log('username');
            error_log($username);
            $profile_img    = @$_FILES["profile_img"]["tmp_name"];
            error_log('profile_img');
            error_log(print_r($profile_img, true));
            $save_filename  = $user_id . "_" . date('Y-m-d-H-i-s') . ".png";

            try {
                error_log('profile_img 3:');    
                if (empty($username) && empty($profile_img)) {
                     // do nothing
                     Controller_V1_Web_Base::error_json('Username and profile_img are empty.');
                     error_log('usernameとprofile_imgが空です');
                } elseif (empty($username)) {
                    // profile update
                    error_log('usernameが空。写真をupdateします');
                    // S3にpictureをupload
                    // Model_Upload::picture_upload($profile_img, $save_filename);
                    Model_S3::input($user_id, $profile_img);
                    Model_User::update_profile_img($user_id, $profile_img);
                } elseif (empty($profile_img)) {
                    // username update
                    error_log('profile_imgが空。ユーザネームをupdateします');
                    Model_User::check_name($username);
                    Model_User::update_name($user_id, $username);
                } else {
                    // Both update
                    error_log('両方updateします');
                    Model_User::check_name($username);
                    // Model_Upload::picture_upload($profile_img, $save_filename);
                    Model_S3::input($user_id, $profile_img);
                    $result = Model_User::update_profile(
                                $user_id, $username, $profile_img
                    );
                }
                $user_data   = Model_User::get_profile($user_id);
                $username    = $user_data['username'];
                error_log('元のusername');
                error_log($username);
                error_log('もとのpicture');
                // $profile_img = $user_daa['profile_img'];
                // error_log($profile_img);
                $data = [
                        "code"        => 200,
                        "message"     => "プロフィールを変更しました",
                        "username"    => $username
                        // "profile_img" => $profile_img    
                ];
   
                $base_data = [
                        "api_version" => 3,
                        "api_uri"     => "/post/update_profile",
                        "api_code"    => 0,
                        "login_falg"  => 1,
                        "api_message" => "success",
                        "api_data"    => $data
                ];
                
                $status = $this->output_json($base_data);
                error_log('json出力');

            } catch (\Database_Exception $e) {
                // self::failed($keyword);
                error_log($e);
            }
        }

	/**
     	* FeedBack
     	*
     	* @return string
     	*/
        public function action_feedback()
        {
            self::create_token($uri="post/fedback", $login_flag=0);
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
	/*
        public function action_restadd()
        {
            self::create_token($uri="post/restadd", $login_flag=0);
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
	*/

	/**
        * Db added Success.
        *
        * @return string
        */
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

	/**
        * Password change
        *
        * @return string
        */
        public function action_password_change()
        {
            // ログインしているユーザIDを取得
            self::create_token($uri="post/password_change", $login_flag=0);
            $user_id = session::get('user_id');

            error_log('パスワードを変更するuser_idは: ');
            error_log($user_id);

            // POST: ユーザーの現在のパスワード
            $current_password = Input::post('current_password');
            // POST: 変更したい新しいパスワード
            $new_password     = Input::post('new_password');
            try {
                // ユーザのIDから登録時のパスワードを取得し、送信されたパスワードとDBパスワード一致するか調べる
                $db_password = Model_User::get_current_db_pass($user_id, $current_password);
                $match_pass = Model_User::web_verify_pass($current_password, $db_password[0]['password']);
        
                // match_passの返り値がTrue     
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
                        "login_falg"  => 1,
                        "api_message" => "success",
                        "api_data" => $data
                    ];

                    $status = $this->output_json($base_data);
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
