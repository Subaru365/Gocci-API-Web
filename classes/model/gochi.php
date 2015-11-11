<?php

/**
 * Gochi Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Gochi extends Model
{
    /**
     * 1投稿のgochi数を求める
     * @param Int $post_id
     *
     * @return Int $gochi_num
     */
    public static function get_num($post_id)
    {
        $query = DB::select('gochi_id')
        ->from ('gochis')
        ->where('gochi_post_id', "$post_id");
        $result    = $query->execute()->as_array();
        $gochi_num = count($result);
        return $gochi_num;
    }

    /**
     * 1投稿に対して自分がgochiしているかを求める
     * @param Int $user_id
     * @param Int $post_id
     *
     * @return Int $gochi_flag
     */
    public static function get_flag($user_id, $post_id)
    {
        $query = DB::select('gochi_id')
        ->from     ('gochis')
        ->where    ('gochi_user_id', "$user_id")
        ->and_where('gochi_post_id', "$post_id");

        $result = $query->execute()->as_array();

        if ($result == true) {
            $gochi_flag = 1;
        } else {
            $gochi_flag = 0;
        }
        return $gochi_flag;
    }

    /**
     * gochi登録
     * @param Int $user_id
     *
     * @return Int $post_id
     */
    public static function post_gochi($user_id, $post_id)
    {
        $query = DB::insert('gochis')
        ->set(array(
            'gochi_user_id' => "$user_id",
            'gochi_post_id' => "$post_id"
        ))
        ->execute();

        $query = DB::select('post_user_id')
        ->from ('posts')
        ->where('post_id', "$post_id");

        $post_user_id = $query->execute()->as_array();

        return $post_user_id[0]['post_user_id'];
    }
}
