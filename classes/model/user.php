<?php
/**
 * User Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_User extends Model
{
    /**
     * ユーザー名とパスワードをチェック
     * @param String $username
     * @param String $password
     */
    public static function check_name_pass($username, $password)
    {
        // username/passwordの両方が空の場合
        if (empty($username) && empty($password)) {
            Controller_V1_Web_Base::error_json('Username and password do not enter.');
        } else if (empty($username) || empty($password)) {
            // usernameもしくはpasswordが空の場合
            Controller_V1_Web_Base::error_json('Username or password do not enter.');
        }
    }

    /**
     * ユーザーIDとユーザー名チェック
     * @param String $username
     * @param String $password
     *
     * @return Bool true
     */
    public static function check_user_IdName($user_id, $username) 
    {
        if (empty($user_id) && (empty($username))) {
            Controller_V1_Web_Base::error_json("user_id and username are empty.");
        }
        return true;
    }

    /**
     * ユーザー名チェック
     * @param String $username
     *
     * @return Array $result
     */
    public static function check_name($username)
    {
        $query = DB::select('username')->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();
        return $result;
    }

    /**
     * ユーザー名チェック[web]
     * @param String $username
     *
     * @return String $username
     */
    public static function check_web_name($username)
    {
        $query = DB::select('username')->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();

        if (!empty($result[0]['username'])) {
             $username = $result[0]['username'];
             error_log('登録されているusername:');
             error_log($username);
             Controller_V1_Web_Base::error_register("username already registered.");
             // 既に登録されているusername
        } else {
             // まだ登録されていないusername
             return $username;
        }
    }

    /**
     * ユーザー名チェック
     * @param String $username
     *
     * @return String $username
     */
    public static function empty_name($username)
    {
        if (empty($username)) {
           // TRUEだとusernameは空である
           return Controller_V1_Web_Base::error_json("Please enter your user name.");
        } else {
           return $username;
        }
    }

    /**
     * パスワード名チェック
     * @param String $password
     *
     * @return String $password
     */
    public static function empty_password($password)
    {
        if (empty($password)) {
            return Controller_V1_Web_Base::error_json("Please enter your password.");
        } else {
            return $password;
        }
    }

    /**
     * ユーザー名形式チェック
     * @param String $username
     *
     * @return String $usename
     */
    public static function format_name_check($username)
    {
        // 文字数チェック
        if (preg_match('/^[a-z\d_]{4,20}$/i', $username)) {
             // 4 - 20文字以内
             return $username;
        } else {
             return Controller_V1_Web_Base::error_json("ユーザーネームは4文字から20文字以内です");
        }
    }

    /**
     * パスワード形式チェック
     * @param String $password
     *
     * @return String $password
     */
    public static function format_password_check($password)
    {
        if (preg_match('/^[A-Za-z0-9]{6,25}\z/', $password)) {
            // 6-25文字
            return $password;
        } else {
            return Controller_V1_Base::error_json("パスワードは6文字以上25文字以内です");
        }
    }

    /**
     * パスワードチェック
     * @param String $username
     * @param String $password
     *
     * @return Array $result
     */
    public static function check_pass($username, $password)
    {
        $query = DB::select('user_id', 'profile_img', 'identity_id', 'badge_num', 'password')
        ->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();

        self::verify_pass($password, $result[0]['password']);

        return $result;
    }

    /**
     * プロフィール画像チェック
     * @param String $profile_img
     *
     * @return Array $result
     */
    public static function check_img($profile_img)
    {
        $query = DB::select('user_id', 'username')->from('users')
        ->where('profile_img', "$profile_img");

        $result = $query->execute()->as_array();

        if (empty($result)) {
            //profile_img該当なし
            Controller_V1_Mobile_Base::output_none();
            error_log("$profile_img" . 'は該当するものがありませんでした。');
            exit;
        }
        return $result[0];
    }

    /**
     * ログインフラグ取得
     * @param Int $user_id
     *
     * @return Int $login_flag
     */
    public static function check_login($user_id)
    {
        $query = DB::select('login_flag')->from('users')
        ->where('user_id', "$user_id");

        $login_flag = $query->execute()->as_array();

        return $login_flag[0]['login_flag'];
    }

    /**
     * user_id取得
     * @param String $username
     *
     * @return Int $user_id
     */
    public static function get_id($username)
    {
        $query = DB::select('user_id')->from('users')
        ->where('username', "$username");

        $user_id = $query->execute()->as_array();

        if (empty($user_id)) {
            $user_id[0]['user_id'] = '';
        }
        return $user_id[0]['user_id'];
    }

    /**
     * 次のuser_id取得
     *
     * @return Int $user_id
     */
    public static function get_next_id()
    {
        $query = DB::select('user_id')->from('users')
        ->order_by('user_id', 'desc')
        ->limit   ('1');

        $result = $query->execute()->as_array();

        $user_id = $result[0]['user_id'];
        $user_id++;

        return $user_id;
    }

    /**
     * identity_id取得
     * @param Int $user_id
     *
     * @return Int $identity_id
     */
    public static function get_identity_id($user_id)
    {
        $query = DB::select('identity_id')->from('users')
        ->where('user_id', "$user_id");

        $identity_id = $query->execute()->as_array();
        return $identity_id[0]['identity_id'];
    }

    /**
     * ユーザー名取得
     * @param Int $user_id
     *
     * @return Int $user_name
     */
    public static function get_name($user_id)
    {
        $query = DB::select('username')->from('users')
        ->where('user_id', "$user_id");

        $username = $query->execute()->as_array();
        return $username[0]['username'];
    }

     /**
     * ユーザー名、プロフィール画像取得
     * @param Int $user_id
     *
     * @return Array $user_data
     */
    public static function get_profile($user_id)
    {
        $query = DB::select('username', 'profile_img')
        ->from('users')
        ->where('user_id', "$user_id");

        $user_data = $query->execute()->as_array();
        $user_data[0]['profile_img'] =
            Model_Transcode::decode_profile_img($user_data[0]['profile_img']);

        return $user_data[0];
    }

    /**
     * パスワード取得
     * @param Int $user_id
     *
     * @return String $password
     */
    public static function get_password($user_id)
    {
        $query = DB::select('password')
                ->from('users')
                ->where('user_id', $user_id);

        $password = $query->execute()->as_array();
        return $password[0]['password'];
    }

     /**
      * 通知数取得
      * @param Int $user_id
      *
      * @return String $password
      */
    public static function get_badge($user_id)
    {
        $query = DB::select('badge_num')->from('users')
        ->where('user_id', "$user_id");

        $user_id = $query->execute()->as_array();
        return $user_id[0]['badge_num'];
    }

     /**
      * ログインユーザー情報取得
      * @param Int $identity_id
      */
    public static function get_auth($identity_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img', 'badge_num')
        ->from ('users')
        ->where('identity_id', "$identity_id");

        $user_data = $query->execute()->as_array();

        if (empty($user_data)) {
            Controller_V1_Mobile_Base::output_none();
            // Cognitoから消去
            Model_Cognito::delete_identity_id($identity_id);
            exit;
        }

        $user_data[0]['profile_img'] =
            Model_Transcode::decode_profile_img($user_data[0]['profile_img']);

        return $user_data[0];
    }

    /**
      * Web用 ログインユーザ情報取得
      * @param Int $identity_id
      *
      * @return Array $user_data
      */
    public static function web_get_auth($identity_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img', 'badge_num')->from('users')
        ->where('identity_id', $identity_id);
        $user_data = $query->execute()->as_array();

        if (empty($user_data)) {
            // ================= コントローラー名を変更する。 =========================

            Controller_V1_Web_Base::error_json('登録されていないユーザです');
            error_log('登録されていないユーザー' . $identity_id);
            // Cognitoから消去
            Model_Cognito::delete_identity_id($identity_id);
            exit;
        }
        $user_data[0]['profile_img'] = Model_Transcode::decode_profile_img($user_data[0]['profile_img']);
        return $user_data[0];
    }

    /**
     * username/passwordからidentity_idを取得する【web】
     * @param String $username
     * @param String $password
     */
    public static function get_web_identity_id($username, $password)
    {
        $query = DB::select('identity_id')
                ->from('users')
                ->where('username', $username)
                ->and_where('password', $password);

        $identity_id = $query->execute()->as_array();
    }

    /**
     * ユーザーページ情報取得
     * @param Int $user_id
     * @param Int $target_user_id
     *
     * @return Array $user_data
     */
    public static function get_data($user_id, $target_user_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img')
        ->from('users')
        ->where('user_id', "$target_user_id");

        $user_data = $query->execute()->as_array();

        if (empty($user_data)) {
            // ユーザーが見つからなかったので、ページが見つかりませんでした。と表示する
            // このページはご利用いただけません。リンクに問題があるか、ページが削除された可能性があります。 Gocciに戻る
            Controller_V1_Web_Base::NotFoundPage();
            exit;
        }
        //付加情報格納(follow_num, fllower_num, cheer_num, status_flag)

        $user_data[0]['profile_img']  = Model_Transcode::decode_profile_img($user_data[0]['profile_img']);
        $user_data[0]['follow_num']   = Model_Follow::follow_num($target_user_id);
        $user_data[0]['follower_num'] = Model_Follow::follower_num($target_user_id);
        $user_data[0]['cheer_num']    = Model_Post::get_user_cheer_num($target_user_id);
        $user_data[0]['want_num']     = Model_Want::want_num($target_user_id);
        $user_data[0]['follow_flag']  = Model_Follow::get_flag($user_id, $target_user_id);

        return $user_data[0];
    }

    /**
     * ユーザー登録
     * @param String $username
     * @param Int $identity_id
     *
     * @return $profile_img
     */
    public static function post_data($username, $identity_id)
    {
        $profile_img = '0_tosty_' . mt_rand(1, 7);

        $query = DB::insert('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    /**
     * username/password登録
     * @param String $username
     * @param Int $identity_id
     * @param String $password
     *
     * @return String $profile_img
     */
    public static function insert_data($username, $identity_id, $password)
    {
        $profile_img = '0_tosty_' . mt_rand(1, 7);

        $query = DB::insert('users')
        ->set(array(
            'username'    => $username,
            'profile_img' => $profile_img,
            'identity_id' => $identity_id,
            'password'    => $password,
        ))
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    /**
     * sns inset
     * @param String $username
     * @param Int $identity_id
     * @param String $profile_img
     *
     * @return String $profile_img
     */
    public static function sns_insert_data($username, $identity_id, $profile_img)
    {
        $query = DB::insert('users')
        ->set(array(
            'username'    => $username,
            'profile_img' => $profile_img,
            'identity_id' => $identity_id,
        ))
        ->execute();
        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    /**
     * @param Int $user_id
     * @param String $password
     *
     * @return Array $query
     */
    public static function update_password($user_id, $password)
    {
        $query = DB::update('users')
        ->value('password', $password)
        ->where('user_id' , $user_id)
        ->execute();

        return $query;
    }

    /**
     * 通知数リセット
     * @param Int $user_id
     *
     * @return Array $query
     */
    public static function reset_badge($user_id)
    {
        $query = DB::update('users')
        ->value('badge_num', '0')
        ->where('user_id', "$user_id")
        ->execute();

        return $query;
    }


    /**
     * パスワードアップデート
     * @param Int $user_id
     * @param String $pass
     *
     * @return Array $query
     */
    public static function update_pass($user_id, $pass)
    {
        $encryption_pass = self::encryption_pass($pass);

        $query = DB::update('users')
        ->value('password', "$encryption_pass")
        ->where('user_id', "$user_id")
        ->execute();

        return $query;
    }

    /**
     * SNS連携
     * @param Int $user_id
     * @param String $provider
     */
    public static function update_sns_flag($user_id, $provider)
    {
        if ($provider == 'graph.facebook.com') {
            $flag = 'facebook_flag';
        } else {
            $flag = 'twitter_flag';
        }

        $query = DB::update('users')
        ->value("$flag", '1')
        ->where('user_id', "$user_id")
        ->execute();
    }

    /**
     * プロフィール画像変更
     * @param Int $user_id
     * @param String $profile_img
     *
     * @return Array $profile_img
     */
    public static function update_profile_img($user_id, $profile_img)
    {
        $query = DB::update('users')
        ->value('profile_img', "$profile_img")
        ->where('user_id', "$user_id")
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }


    /**
     * ユーザー名変更
     * @param Int $user_id
     * @param String $username
     *
     * @return String $username
     */
    public static function update_name($user_id, $username)
    {
        $result = self::check_name($username);

        if (!empty($result)) {
            // username使用済み
            error_log("$username" . 'は既に使用されています。');
            $username = '変更に失敗しました';
        } else {
            $query = DB::update('users')
            ->value('username', "$username")
            ->where('user_id', "$user_id")
            ->execute();
        }
        return $username;
    }

    /**
     * プロフィール画像・ユーザー名変更
     * @param Int $user_id
     * @param String $username
     * @param String $profile_img
     *
     * @return String $username
     */
    public static function update_profile($user_id, $username, $profile_img)
    {
        $query = DB::update('users')
        ->value('profile_img', "$profile_img");

        if ($username != '変更に失敗しました') {
            $query->value('username', "$username");
        }

        $query->where('user_id', "$user_id")
        ->execute();

        return $username;
    }

    /**
     * Logout更新
     * @param Int $user_id
     */
    public static function update_logout($user_id)
    {
        $query = DB::update('users')
        ->value('login_flag', '0')
        ->where('user_id', "$user_id")
        ->execute();
    }

    /**
     * SNS連携しているか確認する
     * @param Int $user_id
     *
     * @return Int $sns_flag
     */
    public static function check_sns_flag($user_id)
    {
        $query = DB::select('facebook_flag', 'twitter_flag')
                ->from('users')
                ->where('user_id', $user_id);

        return $sns_flag = $query->execute()->as_array();
    }

    /**
     * SNS連携
     *
     * @param Int $user_id
     * @param String $provider
     */
    public static function delete_sns_flag($user_id, $provider)
    {
        if ($provider == 'graph.facebook.com') {
            $flag = 'facebook_flag';
        } else {
            $flag = 'twitter_flag';
        }

        $query = DB::update('users')
        ->value("$flag", '0')
        ->where('user_id', "$user_id")
        ->execute();
    }

    /**
     * パスワードハッシュ化
     *
     * @param String $pass
     *
     * @return $hash_pass
     */
    private static function encryption_pass($pass)
    {
        $hash_pass = password_hash($pass, PASSWORD_BCRYPT);
        return $hash_pass;
    }

    /**
     * ハッシュ化パスワードが一致するか
     *
     * @param String $pass
     * @param String $hash_pass
     */
    private static function verify_pass($pass, $hash_pass)
    {
        if (password_verify($pass, $hash_pass)) {
            // 認証OK
        } else {
            $data = [
               'message' => 'パスワードが一致しません',
        ];
        $base_data      = Controller_V1_Web_Base::base_template($api_code = "ERR_SIGNIN", $api_message = "パスワードが一致しません", $login_flag =  1, $data, $jwt = "");
        $status = Controller_V1_Web_Base::output_json($base_data);
        exit;
        }
    }

    /**
     * ハッシュ化パスワードが一致するか[web]
     *
     * @param String $pass
     * @param String $hash_pass
     *
     * @return $match_pass
     */
    public static function web_verify_pass($pass, $hash_pass)
    {
        if (password_verify($pass, $hash_pass)) {
            // 認証OK
            $match_pass = password_verify($pass, $hash_pass);
        } else{
            Controller_V1_Web_Base::error_json("パスワードが正しくありません");
            exit;
        }
        return $match_pass;
    }

    /**
     * DBのハッシュ化パスワード取得
     *
     * @param Int $user_id
     * @param String $current_password
     *
     * @return String $db_password
     */
    public static function get_current_db_pass($user_id, $current_password)
    {
        // ユーザから送られてきた生パスワードをエンクリプト
        $encrypt_password = self::encryption_pass($current_password);
        // dbからパスワードを取得
        $query = DB::select('password')
                ->from('users')
                ->where('user_id', $user_id);
        return $db_password = $query->execute()->as_array();
    }

    /**
     * Conversionチェック
     *
     * @param String $username
     *
     * @return Arrat $query
     */
    public static function check_conversion($username)
    {
        $query = DB::select('user_id')->from('users')
        ->where('username', "$username")
        ->execute()->as_array();

        return $query;
    }

    /**
     * ユーザー登録
     *
     * @param Int $user_id
     * @param String $username
     * @param String $profile_img
     * @param Int $identity_id
     *
     * @return $profile_img
     */
    public static function post_conversion(
        $user_id, $username, $profile_img, $identity_id)
    {
        $query = DB::insert('users')
        ->set(array(
            'user_id'     => "$user_id",
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    /**
     * ユーザーデータ更新
     *
     * @param Int $user_id
     * @param String $username
     * @param String $profile_img
     * @param Int $identity_id
     *
     * @return $profile_img
     */
    public static function update_data(
        $user_id, $username, $profile_img, $identity_id)
    {
        $query = DB::update('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->where('user_id', "$user_id")
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    /**
     * web: username/password check
     *
     * @param String $username
     * @param String $password
     */
    public static function pass_login_validate($username, $passsword)
    {
        $val = Validation::forge();
        $val->add_field('username', 'ユーザーネーム', 'required');
        $val->add_field('password', 'パスワード', 'required');
    }

    /**
     * web username/profile_img 空かどうかチェック
     *
     * @param String $username
     * @param String $profile_img
     */
    public static function username_profile_img_check($username, $profile_img)
    {
        if (empty($username) || empty($profile_img)) {
              Controller_V1_Web_Base::error_json('Username and profile_img do not enter.');
        }
    }
}
