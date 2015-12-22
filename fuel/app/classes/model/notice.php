<?php
/**
 * Notice Class Api
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Notice extends Model
{
    /**
     * ユーザーの通知を取得する
     * @param Int $user_id
     *
     * @param Array $notice_data
     */
    public static function get_data($user_id)
    {
        $query = DB::select(
         'notice_id', 'notice_a_user_id','post_id', 'post_hash_id', 'username',
         'profile_img', 'notice', 'notice_post_id',
         'read_flag', 'notice_date')
        ->from('notices')

        ->join('users', 'INNER')
        ->on('notices.notice_a_user_id', '=', 'users.user_id')

        ->join('posts', 'INNER')
        ->on('notice_post_id', '=', 'post_id')

        ->order_by('notices.notice_date','desc')

        ->limit('15')
        ->where('notices.notice_p_user_id', "$user_id");
        // ->where('post_status_flag', '1');

        $notice_data = $query->execute()->as_array();
        $num = count($notice_data);

        for ($i=0; $i < $num; $i++) {
            // 日付情報を現在との差分に書き換え
            $notice_data[$i]['notice_date'] = Model_Date::get_data($notice_data[$i]['notice_date']);
            $notice_data[$i]['profile_img'] = Model_Transcode::decode_profile_img($notice_data[$i]['profile_img']);
        }
        return $notice_data;
    }

    /**
     * badge数取得
     * @param Int $user_id
     *
     * @return $query
     */
    public static function get_badge($user_id)
    {
        $query = DB::select('badge_num')->from('users')
        ->where('user_id', '=', $user_id)
        ->execute()->as_array();

        if ( isset($query[0]['badge_num'])) {
            return $query[0]['badge_num'];
        }
    }

    /**
     * Notice登録
     * @param Int $a_user_id
     * @param Int $p_user_id
     * @param Int $post_id
     */
    public static function notice_insert(
            $keyword, $a_user_id, $p_user_id, $post_id = 1)
    {
        if ($keyword === 'gochi') {
            $notice = 'like';
        } elseif ($keyword === 'コメント') {
            $notice = 'comment';
        } elseif ($keyword === 'フォロー') {
            $notice = 'follow';
        } else{
            $notice = 'announce';
        }
        $query = DB::insert('notices')
        ->set(array(
            'notice_a_user_id' => "$a_user_id",
            'notice_p_user_id' => "$p_user_id",
            'notice'           => "$notice",
            'notice_post_id'   => "$post_id"
        ))
        ->execute();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            'http://localhost:3000/v1/mobile/background/publish/'
            // 'http://localhost:3000/v1/background/publish/'
            .'?keyword='   . "$keyword"
            .'&a_user_id=' . "$a_user_id"
            .'&p_user_id=' . "$p_user_id"
        );
        curl_exec($ch);
        curl_close($ch);
    }
}
