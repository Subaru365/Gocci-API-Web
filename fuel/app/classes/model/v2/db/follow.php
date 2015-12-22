<?php
/**
 * Follow Model
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V2_DB_Follow extends Model
{
    use GocciAPI;

    private static $_table_name = 'follows';

    /**
     * followしているuser_idリスト
     * @param Int $user_id
     *
     * @return Int $follow_id
     */
    public static function getFollowId($user_id)
    {
        $query = DB::select('follow_p_user_id')
          ->from('follows')
          ->where('follow_a_user_id', $user_id)

        $follow_id = $query->execute()->as_array();

        if (empty($follow_id)) {
            GocciAPI::error_json('NOT_FOLLOW');
            exit;
        }
        return $follow_id;
    }

    /**
     * followしているユーザー情報
     * @param  Int $user_id
     * @param  Int $target_user_id
     *
     * @return Int $follow_list
     */
    public static function getFollow($user_id, $target_user_id)
    {
        $query = DB::select(
          'user_id', 'username', 'profile_img'
        )
        ->from('follows')
        ->join('users', 'INNER')
        ->on('follow_p_user_id', '=', 'user_id')
        ->where('follow_a_user_id', $target_user_id);

        $follow_list = $query->execute()->as_array();
        $follow_num = count($follow_list);

        for ($i=0; $i < $follow_num; $i++) {
            $follow_list[$i]['profile_img'] = Model_Transcode::decode_profile_img($follow_list[$i]['profile_img']);
            $follow_list[$i]['follow_flag'] = self::getFlag($user_id, $follow_list[$i]['user_id']);
        }
        return $follow_list;
    }
    /**
     * フォローされてるユーザー情報
     * @param  Int $user_id
     * @param  Int $target_user_id
     *
     * @return Int $follower_list
     */
    public static function getFollower($user_id, $target_user_id)
    {
        $query = DB::select(
            'user_id', 'username', 'profile_img'
        )
        ->from ('follows')
        ->join ('users', 'INNER')
        ->on   ('follow_a_user_id', '=', 'user_id')
        ->where('follow_p_user_id', "$target_user_id");

        $follower_list = $query->execute()->as_array();
        $follower_num  = count($follower_list);

        for ($i=0; $i < $follower_num; $i++) {
            $follower_list[$i]['profile_img'] =
            Model_Transcode::decode_profile_img($follower_list[$i]['profile_img']);
            $follower_list[$i]['follow_flag'] = self::get_flag($user_id, $follower_list[$i]['user_id']);
        }
        return $follower_list;
    }

    /**
     * 相手のユーザーに対してフォローしてるかフラグで返す
     * @param  Int $user_id
     * @param  Int $target_user_id
     *
     * @return Int $follow_flag
     */
    public static function getFlag($user_id, $target_user_id)
    {
        $query = DB::select('follow_id')
        ->from('follows')
        ->where('follow_a_user_id', $user_id)
        ->and_where('follow_p_user_id', $target_user_id);

        $result = $query->execute()->as_array();

        if ($result == true) {
            $follow_flag = 1;
        } else {
            $follow_flag = 0;
        }
        return $follow_flag;
    }

    /**
     * フォロー数を返す
     * @param  Int $user_id
     *
     * @return Int $follow_num
     */
    public static function followNum($user_id)
    {
        $query = DB::select('follow_id')
        ->from ('follows')
        ->where('follow_a_user_id', "$user_id");

        $result = $query->execute()->as_array();
        $follow_num = count($result);
        return $follow_num;
    }

    /**
     * フォロワー数を返す
     * @param  Int $user_id
     * @return Int $follow_num
     */
    public static function followerNum($user_id)
    {
        $query = DB::select('follow_id')
        ->from ('follows')
        ->where('follow_p_user_id', "$user_id");

        $result = $query->execute()->as_array();
        $follower_num = count($result);
        return $follower_num;
    }

    /**
     * フォロワー登録
     * @param  Int $user_id
     * @param  Int $target_user_id
     *
     * @return Int $follow_num
     */
    public static function postFollow($user_id, $target_user_id)
    {
        $query = DB::insert('follows')
        ->set(array(
            'follow_a_user_id' => "$user_id",
            'follow_p_user_id' => "$target_user_id"
        ));

        $result = $query->execute();
        return $result;
    }

    /**
     * フォロー解除
     * @param  Int $user_id
     * @param  Int $target_user_id
     *
     * @return Int $follow_num
     */
    public static function postUnfollow($user_id, $target_user_id)
    {
        $query = DB::delete('follows')
        ->where     ('follow_a_user_id', "$user_id")
        ->and_where ('follow_p_user_id', "$target_user_id");

        $result = $query->execute();
        return $result;
    }
}