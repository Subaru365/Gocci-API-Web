<?php
/**
 * User Model Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V2_DB_User extends Model
{
    use GocciAPI;

    private static $_table_name = 'users';

    /**
     * ユーザー名とパスワードをチェック
     * @param String $username
     * @param String $password
     */
    public static function checkNamePass($username, $password)
    {
        if (empty($username) && empty($password)) {
            GocciAPI::error_json('username and password do not enter.');
        } else if (empty($username) || empty($password)) {
            GocciAPI::error_json('username or password do not enter.');
        }
    }

    /**
     * ユーザーIDとユーザー名チェック
     * @param String $username
     * @param String $password
     *
     * @return Bool true
     */
    public static function checkUserIdName($user_id, $username)
    {
        if (empty($user_id) && (empty($username))) {
            GocciAPI::error_json("user_id and username are empty.");
        }
        return true;
    }

    /**
     * ユーザー名チェック
     * @param String $username
     *
     * @return String $username
     */
    public static function checkName($username)
    {
        $query = DB::select('username')->from('users')
        ->where('username', $username);

        $result = $query->execute()->as_array();

        if (!empty($result[0]['username'])) {
            GocciAPI::error_register("username already registerd.");
        } else {
            return $username;
        }
    }

    /**
     * パスワードチェック
     * @param String $username
     * @param String $password
     *
     * @return Array $result
     */
    public static function checkPass($username, $password)
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
    public static function checkImg($profile_img)
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
    public static function checkLogin($user_id)
    {
        $query = DB::select('login_flag')->from('users')
        ->where('user_id', "$user_id");

        $login_flag = $query->execute()->as_array();

        return $login_flag[0]['login_flag'];
    }

    /**
     * ユーザー名チェック
     * @param String $username
     *
     * @return String $username
     */
    public static function emptyName($username)
    {
        if (empty($username)) {
           // TRUEだとusernameは空である
           return GocciAPI::error_json("Please enter your user name.");
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
    public static function emptyPassword($password)
    {
        if (empty($password)) {
            return GocciAPI::error_json("Please enter your password.");
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
    public static function formatNameCheck($username)
    {
        // 文字数チェック
        if (preg_match('/^[a-z\d_]{4,20}$/i', $username)) {
             // 4 - 20文字以内
             return $username;
        } else {
             return GocciAPI::error_json("ユーザーネームは4文字から20文字以内です");
        }
    }

     /**
     * パスワード形式チェック
     * @param String $password
     *
     * @return String $password
     */
    public static function formatPasswordCheck($password)
    {
        if (preg_match('/^[A-Za-z0-9]{6,25}\z/', $password)) {
            // 6-25文字
            return $password;
        } else {
            return GocciAPI::error_json("パスワードは6文字以上25文字以内です");
        }
    }

    /**
      * ログインユーザ情報取得
      * @param Int $identity_id
      *
      * @return Array $user_data
      */
    public static function getAuth($identity_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img', 'badge_num')->from('users')
        ->where('identity_id', $identity_id);
        $user_data = $query->execute()->as_array();

        if (empty($user_data)) {
            GocciAPI::error_json('登録されていないユーザです');
            // Cognitoから消去
            Model_Cognito::delete_identity_id($identity_id);
            exit;
        }
        $user_data[0]['profile_img'] = Model_Transcode::decode_profile_img($user_data[0]['profile_img']);
        return $user_data[0];
    }

      /**
     * user_id取得
     * @param String $username
     *
     * @return Int $user_id
     */
    public static function getId($username)
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
    public static function getNextId()
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
     * username/passwordからidentity_idを取得する
     * @param String $username
     * @param String $password
     */
    public static function getIdentityId($username, $password)
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
    public static function getData($user_id, $target_user_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img')
        ->from('users')
        ->where('user_id', "$target_user_id");

        $user_data = $query->execute()->as_array();

        if (empty($user_data)) {
            GocciAPI::NotFoundPage();
            exit;
        }

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
    public static function postData($username, $identity_id)
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
    public static function insertData($username, $identity_id, $password)
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
    public static function snsInsertData($username, $identity_id, $profile_img)
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
    public static function updatePassword($user_id, $password)
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
    public static function resetBadge($user_id)
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
    public static function updatePass($user_id, $pass)
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
    public static function updateSnsFlag($user_id, $provider)
    {
        if ($provider == 'graph.facebook.com') {
            $flag = 'facebook_flag';
        } else {
            $flag = 'twitter_flag';
        }

        $query = DB::update('users')
        ->value($flag, '1')
        ->where('user_id', $user_id)
        ->execute();
    }

    /**
     * プロフィール画像変更
     * @param Int $user_id
     * @param String $profile_img
     *
     * @return Array $profile_img
     */
    public static function updateProfileImg($user_id, $profile_img)
    {
        $query = DB::update('users')
        ->value('profile_img', $profile_img)
        ->where('user_id', $user_id)
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
    public static function updateName($user_id, $username)
    {
        $result = self::check_name($username);

        if (!empty($result)) {
            // username使用済み
            $username = '変更に失敗しました';
        } else {
            $query = DB::update('users')
            ->value('username', $username)
            ->where('user_id', $user_id)
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
    public static function updateProfile($user_id, $username, $profile_img)
    {
        $query = DB::update('users')
        ->value('profile_img', $profile_img);

        if ($username != '変更に失敗しました') {
            $query->value('username', $username);
        }

        $query->where('user_id', $user_id)
        ->execute();

        return $username;
    }

    /**
     * Logout更新
     * @param Int $user_id
     */
    public static function updateLogout($user_id)
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
    public static function checkSnsFlag($user_id)
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
    public static function deleteSnsFlag($user_id, $provider)
    {
        if ($provider == 'graph.facebook.com') {
            $flag = 'facebook_flag';
        } else {
            $flag = 'twitter_flag';
        }

        $query = DB::update('users')
        ->value($flag, '0')
        ->where('user_id', $user_id)
        ->execute();
    }

    /**
     * パスワードハッシュ化
     *
     * @param String $pass
     *
     * @return $hash_pass
     */
    private static function encryptionPass($pass)
    {
        $hash_pass = password_hash($pass, PASSWORD_BCRYPT);
        return $hash_pass;
    }

    /**
     * ハッシュ化パスワードが一致するか
     *
     * @param String $pass
     * @param String $hash_pass
     *
     * @return $match_pass
     */
    public static function verifyPass($pass, $hash_pass)
    {
        if (password_verify($pass, $hash_pass)) {
            // 認証OK
            $match_pass = password_verify($pass, $hash_pass);
        } else{
            GocciAPI::error_json("パスワードが正しくありません");
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
    public static function getCurrentDbPass($user_id, $current_password)
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
    public static function checkConversion($username)
    {
        $query = DB::select('user_id')->from('users')
        ->where('username', $username)
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
    public static function postConversion(
        $user_id, $username, $profile_img, $identity_id)
    {
        $query = DB::insert('users')
        ->set(array(
            'user_id'     => $user_id,
            'username'    => $username,
            'profile_img' => $profile_img,
            'identity_id' => $identity_id
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
    public static function updateData(
        $user_id, $username, $profile_img, $identity_id)
    {
        $query = DB::update('users')
        ->set(array(
            'username'    => $username,
            'profile_img' => $profile_img,
            'identity_id' => $identity_id
        ))
        ->where('user_id', $user_id)
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    /**
     * username/password check
     *
     * @param String $username
     * @param String $password
     */
    public static function passLoginValidate($username, $passsword)
    {
        $val = Validation::forge();
        $val->add_field('username', 'ユーザーネーム', 'required');
        $val->add_field('password', 'パスワード', 'required');
    }

    /**
     * username/profile_img 空かどうかチェック
     *
     * @param String $username
     * @param String $profile_img
     */
    public static function username_profile_img_check($username, $profile_img)
    {
        if (empty($username) || empty($profile_img)) {
              GocciAPI::error_json('Username and profile_img do not enter.');
        }
    }
}