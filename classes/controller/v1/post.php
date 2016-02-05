<?php
/**
 * POST Api    リソースの新規作成
 * @package    Gocci-Web
 * @version    3.0 <2015/12/22>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V1_Post extends Controller_V1_Base
{
    /**
     * @var Array $data
     */
    private $data = [];

    /**
     * @var String $msg
     */
    private $msg;

    /**
     * @return Array $data
     */
    public function getData()
    {
        return $this->$data;
    }

    /**
     * @param String $msg
     */
    public function setData($msg)
    {
        $this->data = ["message" => "$msg"];
    }

    /**
     * jwt check
     * @param string POST $uri
     * @param string POST $login_flag
     */
    public static function create_token($uri="", $login_flag)
    {
        $jwt = self::get_jwt();
        if(isset($jwt) || !empty($jwt)) {
            // error_log('ここ1');
            if (empty($jwt)) {
                error_log('jwtが空です');
            }
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);
            if (empty($obj)) {
                error_log('not obj!');
                self::unauth();
            } else {
                error_log('Object is Exists!');
            }
            $user_id   = $obj->{'user_id'};
            $username  = $obj->{'username'};
            $exp       = $obj->{'exp'};
            session::set('user_id', $user_id);
            session::set('username', $username);
            session::set('exp', $exp);
        } else {
            error_log('jwt is not set.');
            self::unauth();
            exit;
        }
    }

    /**
     * @return $user_id
     */
    public function notLoginGetUserId() {
        $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";
        if ( isset($jwt) ) {
            $obj = self::runDeocd($jwt);
            if (empty($obj)) {
                $user_id = 1;
            } else {
                $user_id  = $obj->{'user_id'};
            }
        } else {
            $user_id = 1;
        }
        return $user_id;
    }

    /**
     * SNS連携
     */
    public function action_sns_link()
    {
        error_log('sns_link');
        if (!isset($_GET['token'])) {
             self::create_token($uri=Uri::string(), $login_flag=0);
        }

        $keyword     = 'SNS連携';
        $user_id     = session::get('user_id');
        $provider    = Input::get('provider');
        $token       = Input::get('token');
        $profile_img = Input::get('profile_img');

        try {
            if (empty($user_id)) {
                $user_id = $_GET['user_id'];
                error_log('getで取得したuser_id');
                error_log($user_id);
            }
            $identity_id = Model_User::get_identity_id($user_id);
            Model_User::update_sns_flag($user_id, $provider);
            Model_Cognito::post_sns($user_id, $identity_id, $provider, $token);

            $data = [
                "profile_img" => $profile_img
            ];
            if (empty($jwt)) {
                $jwt = "";
            }
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag  =  1, $data, $jwt
            );
            if ($provider === "api.twitter.com") {
                // twitter
                $json = self::assignment_json($base_data);
                // header('Location: http://127.0.0.1:3000/#/setting/cooperation/?json='. $json); // test
                header("Location: " .self::CALLBACK_SETTING_URL.$json);
                exit;
            }

            error_log('facebook jsonを返します');
            self::output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * sns解除
     */
    public function action_unlink()
    {
        error_log('unlink');
        $keyword  = 'SNS連携解除';
        $user_id     = Input::get('user_id');
        $provider    = Input::get('provider');
        $token       = Input::get('token');
        $profile_img = Input::get('profile_img');

        try {
            if (empty($provider) && empty($token) || empty($provider) || empty($token)) {
                error_log($user_id);
                error_log($provider);
                error_log($token);
                error_log($profile_img);

                error_log("POSTされていない値があります");
                self::error_json('UnAuthorized');
                // self::failed($message = "POSTされていない値があります");
                exit;
            }
            // 他にSNS連携しているか確認
            $sns_flag = Model_User::check_sns_flag($user_id);
            $facebook_flag = $sns_flag[0]['facebook_flag'];
            $twitter_flag  = $sns_flag[0]['twitter_flag'];
            $password = Model_User::get_password($user_id);

            if (empty($password)) {
                self::failed($message = "パスワードを登録してください SNS解除");
            } else if ((int)$facebook_flag === (int)1 || (int)$twitter_flag === (int)1) {
                self::start_unlink($user_id, $provider, $token, $keyword);
            } else {
                // パスワードは既に登録されている
                self::start_unlink($user_id, $provider, $token, $keyword);
            }
        } catch (\Dataase_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * 現在何を連携しているのかを
     */
    public static function action_check_sns_coordination()
    {
        error_log('action_check_sns_coordination');
        self::create_token($uri=Uri::string(), $login_flag=1);
        $user_id  = session::get('user_id');
        error_log('user_id');
        error_log($user_id);
        $sns_flag = Model_User::check_sns_flag($user_id);
        $facebook_flag = $sns_flag[0]['facebook_flag'];
        $twitter_flag  = $sns_flag[0]['twitter_flag'];
        $password = Model_User::get_password($user_id);
        if (empty($password)) {
            $message = "UNREGISTER";
        } else {
            $message = "REGISTER";
        }
        $data = [
            "facebook_flag" => (int)$facebook_flag,
            "twitter_flag"  => (int)$twitter_flag,
            "message"       => $message
        ];
        $base_data = self::base_template($api_code = 0, 
            $api_message = "SUCCESS",
            $login_flag  =  1, $data, $jwt = ""
        );
        $json = self::assignment_json($base_data);
        error_log('check_sns_coordinationのjson');
        error_log(print_r($json, true));
        $status = self::output_json($base_data);
    }

    /**
     * パスワードが登録されているか確認
     */
    public static function action_password_check()
    {
        self::create_token($uri=Uri::string(), $login_flag=1);
        $user_id  = session::get('user_id');
        $password = Model_User::get_password($user_id);

        if (empty($password)) {
            $data = [
                "message" => "UNREGISTER"
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                $api_message = "UnAuthorized",
                $login_flag =  1, $data, $jwt=""
            );
            self::output_json($base_data);
        } else {
            $data = [
                "message" => "REGISTER"
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                $api_message = "UnAuthorized", 
                $login_flag  =  1, $data, $jwt=""
            );
            self::output_json($base_data);
        }
    }

    /**
     * パスワード設定
     */
    public static function action_create_password()
    {
        self::create_token($uri=Uri::string(), $login_flag=1);
        $user_id  = session::get('user_id');
        $password = Input::post('password');

        if (empty($password)) {
            self::failed($message = "passwordが空です。パスワードが生成");
        }

        $password  = Model_User::format_password_check($password);
        $hash_pass = password_hash($password, PASSWORD_BCRYPT);

        try {
            Model_User::update_password($user_id, $hash_pass);
            $data = [
                "message" => "パスワードを登録しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag =  1, $data, $jwt=""
            );

            $status = self::output_json($base_data);
            error_log("パスワードを登録しました");
        } catch (\Database_Exception $e) {
            error_log($e);
        }
    }

    /**
     * @param string POST $user_id
     * @param string POST $provider
     * @param string POST $token
     * @param string POST $keyword
     */
    public static function start_unlink($user_id, $provider, $token, $keyword)
    {
        try {
            error_log('user_id');
            error_log($user_id);
            $identity_id = Model_User::get_identity_id($user_id);
            error_log('unlink_1');
            Model_User::delete_sns_flag($user_id, $provider);
            error_log('unlink_2');
            Model_Cognito::delete_sns($user_id, $identity_id, $provider, $token);
            error_log('unlink_3');
            $data = [
                "message" => "連携解除しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag  =  1, $data, $jwt=""
            );
            if ($provider === "api.twitter.com") {
                // twitter
                $json = self::assignment_json($base_data);
                // header('Location: http://127.0.0.1:3000/#/setting/cooperation/?json='. $json); // test
                // header('Location: http://gocci.me/#/setting/cooperation/?json='.$json); // production
                header("Location: " .self::CALLBACK_SETTING_URL.$json);
                exit;
            }
            // error_log('Facebook jsonを返します unlink');
            $json = self::assignment_json($base_data);
            error_log($json);
            $status = self::output_json($base_data);
        } catch (\Dataase_Exception $e) {
            error_log('Error');
             self::failed($keyword);
             error_log($e);
        }
    }

    /**
     * Gochi->Like
     * @param string POST $uri
     * @param string POST $login_flag
     */
    public function action_gochi()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword = 'gochi';
        $user_id = session::get('user_id');
        $post_id = Input::post('post_id');

        try {
            $target_user_id = Model_Gochi::post_gochi(
                $user_id, $post_id
            );
            if ((int)$user_id !== (int)$target_user_id) {
                $record = Model_Notice::notice_insert(
                    $keyword, $user_id, $target_user_id, $post_id
                );
            }
            $data = [
                "message" => "gochiしました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );
            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    public function action_ungochi()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword = 'gochi';
        $user_id = session::get('user_id');
        $post_id = Input::post('post_id');

        try {
            $target_user_id = Model_Gochi::post_ungochi(
                $user_id, $post_id
            );
            if ((int)$user_id !== (int)$target_user_id) {
                $record = Model_Notice::notice_insert(
                    $keyword, $user_id, $target_user_id, $post_id
                );
            }
            $data = [
                "message" => "gochiを取り消しました。"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );
            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * comment
     */
    public function action_comment()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword = 'コメント';
        $user_id = session::get('user_id');
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
            $data = [
                "message" => "コメントしました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );

            $status = $this->output_json($base_data);
        } catch(\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * Follow
     */
    public function action_follow()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword        = 'フォロー';
        $user_id        = session::get('user_id');
        $follow_user_id = Input::post('target_user_id');

        error_log('user_id');
        error_log($user_id);

        error_log('follow_user_id');
        error_log($follow_user_id);

        try {
            // 既にユーザーをフォローしていないかチェックする
            Model_Follow::check_follow($user_id, $follow_user_id);
            $data = Model_Follow::post_follow($user_id, $follow_user_id);
            // $data = Model_Follow::get_follower($user_id, $follow_user_id);
            error_log(print_r($data, true));
            $record = Model_Notice::notice_insert(
                $keyword, $user_id, $follow_user_id
            );
            /*
            $data = [
                "message" => "フォローしました"
            ];
            */

            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag  = 1, $data, $jwt = ""
            );
            $status = $this->output_json($base_data);
            $json = self::assignment_json();
            error_log(print_r($json, true));
        } catch(\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * UnFollow
     * @return string
    */
    public function action_unfollow()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword          = 'フォローを解除';
        $user_id          = session::get('user_id');
        $unfollow_user_id = Input::post('target_user_id');

        try {
            $result = Model_Follow::post_unfollow($user_id, $unfollow_user_id);

            $data = [
                "message" => "フォロー解除しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                $api_message = "Successful API request", 
                $login_flag  = 1, $data, $jwt = ""
            );

            $status = $this->output_json($base_data);

        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * Want
     */
    public function action_want()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword = '行きたい店リストに追加';
        $user_id = session::get('user_id');
        $rest_id = Input::post('rest_id');
        try {
            $result = Model_Want::post_want($user_id, $rest_id);
            $data = [
                "message" => "行きたいリストに追加しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request",
                $login_flag  = 1, $data, $jwt = ""
            );
            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

   /**
    * UnWant
    */
    public function action_unwant()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword = '行きたい店リストから削除';
        $user_id = session::get('user_id');
        $rest_id = Input::post('rest_id');

        try {
            $result = Model_Want::post_unwant($user_id, $rest_id);
            $data = [
                "message" => "行きたい店リストから削除しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );

            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * PostBlock
     */
    public function action_postblock()
    {
        $keyword = '違反報告';
        $user_id = self::notLoginGetUserId();
        $post_id = Input::post('post_id');

        try {
            $result = Model_Block::post_block($user_id, $post_id);
            $data = [
                "message" => "投稿を違反報告しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request",
                $login_flag = 1, $data, $jwt = ""
            );
            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * PostDelete
     */
    public function action_postdel()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword = '投稿を消去';
        $post_id = Input::post('post_id');

        try {
            $result = Model_Post::post_delete($post_id);
            $data = [
                "message" => "投稿を消去しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );

            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * Profile Edit
     */
    public function action_update_profile()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword        = 'プロフィールを変更';
        $user_id        = session::get('user_id');
        $username       = Input::post('username');
        $profile_img    = @$_FILES["profile_img"]["tmp_name"];
        $save_filename  = $user_id . "_" . date('Y-m-d-H-i-s') . ".png";

        try {
            if (empty($username) && empty($profile_img)) {
                 Controller_V1_Web_Base::error_json('Username and profile_img are empty.');
            } elseif (empty($username)) {
                Model_S3::input_img($user_id, $profile_img);
                Model_User::update_profile_img($user_id, $profile_img);
            } elseif (empty($profile_img)) {
                Model_User::check_name($username);
                Model_User::update_name($user_id, $username);
            } else {
                Model_User::check_name($username);
                $profile_img = Model_S3::input_img($user_id, $profile_img);
                $result = Model_User::update_profile(
                    $user_id, $username, $profile_img
                );
            }
            $user_data   = Model_User::get_profile($user_id);
            $username    = $user_data['username'];
            error_log('更新後のprofile_img');
            error_log($profile_img);
            $data = [
                "message"     => "プロフィールを変更しました",
                "username"    => $username,
                "profile_img" => $profile_img
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );

            $status = $this->output_json($base_data); 

        } catch (\Database_Exception $e) {
            error_log($e);
        }
    }

    /**
     * FeedBack
     */
    public function action_feedback()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $keyword  = '意見を投稿';
        $user_id  = session::get('user_id');
        $feedback = Input::post('feedback');
        try {
            $result = Model_Feedback::post_add($user_id, $feedback);
            $data = [
                "message" => "ご意見を投稿しました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag = 1, $data, $jwt = ""
            );

            $status = $this->output_json($base_data);
        } catch(\Database_Exception $e) {
            self::failed($keyword);
            error_log($e);
        }
    }

    /**
     * Db added SUCCESS.
     * @param $message
     */

    // DB Error
    private static function failed($message)
    {
        $api_data = [
            "api_version" => 3.0,
            "api_uri"     => Uri::string(),
            "api_code"    => " VALIDATION ERROR",
            "api_message" => $message,# . "できませんでした",
            "login_flag"  => 1,
            "api_data"    => $obj = new stdClass()
        ];

        self::output_json($api_data);
        exit;
    }

    /**
     * Password change
     */
    public function action_password_change()
    {
        // ログインしているユーザIDを取得
        self::create_token($uri=Uri::string(), $login_flag=0);
        $user_id = session::get('user_id');
        $current_password = Input::post('current_password');
        $new_password     = Input::post('new_password');

        try {
            // ユーザのIDから登録時のパスワードを取得し、送信されたパスワードとDBパスワード一致するか調べる
            $db_password = Model_User::get_current_db_pass($user_id, $current_password);
            $match_pass = Model_User::web_verify_pass($current_password, $db_password[0]['password']);
            if ($match_pass) {
                // 一致
                Model_User::update_pass($user_id, $new_password);
                // 正式に変更できたら、jsonでパスワードを変更しましたを含むJSONを吐く
                $data = [
                    "message" => "パスワードを変更しました"
                ];
                $base_data = self::base_template($api_code = 0, 
                    $api_message = "SUCCESS", 
                    $login_flag =  1,$data, $jwt=""
                );

                $status = $this->output_json($base_data);
            }  else {
                Controller_V1_Web_Base::error_json("パスワードが正しくありません");
                exit;
            }
        } catch (\Database_Exception $e) {
            error_log($e);
            exit;
        }
    }
}