<?php
/**
 * Want Class 
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_Want extends Model
{
    /**
     * ユーザーの行きたい取得
     * @param Int $user_id
     *
     * @return Array $want_list
     */
    public static function get_want($user_id)
    {
        $query = DB::select(
            'rest_id', 'restname', 'locality'
        )
        ->from('wants')
        ->join('restaurants', 'INNER')
        ->on('want_rest_id', '=', 'rest_id')
        ->where('want_user_id', "$user_id");

        $want_list = $query->execute()->as_array();
        return $want_list;
    }

    /**
     * ユーザーのレストランFlag取得
     * @param Int $user_id
     * @param Int $post_rest_id
     *
     * @return Array $want_flag
     */
    public static function get_flag($user_id, $post_rest_id)
    {
        $query = DB::select('want_id')->from('wants')
        ->where         ('want_user_id', "$user_id")
        ->and_where ('want_rest_id', "$post_rest_id");

        $result = $query->execute()->as_array();

        if ($result == true) {
            $want_flag = 1;
        } else {
            $want_flag = 0;
        }
        return $want_flag;
    }

    /**
     * ユーザーの行きたい数取得
     * @param Int $user_id
     *
     * @return Int $want_num
     */
    public static function want_num($user_id)
    {
        $query = DB::select('want_id')->from('wants')
        ->where('want_user_id', "$user_id");

        $result = $query->execute()->as_array();
        $want_num = count($result);
        return $want_num;
    }

    /**
     * 行きたい登録
     * @param Int $user_id
     * @param Int $want_rest_id
     *
     * @return Array $result
     */
    public static function post_want($user_id, $want_rest_id)
    {
        $query = DB::insert('wants')
        ->set(array(
            'want_user_id' => "$user_id",
            'want_rest_id' => "$want_rest_id"
        ));
        $result = $query->execute();
        return $result;
    }

    /**
     * 行きたいお店解除
     * @param Int $user_id
     * @param Int $want_rest_id
     *
     * @return Array $result
     */
    public static function post_unwant($user_id, $want_rest_id)
    {
        $query = DB::delete('wants')
        ->where     ('want_user_id', "$user_id")
        ->and_where ('want_rest_id', "$want_rest_id");

        $result = $query->execute();
        return $result;
    }
}
