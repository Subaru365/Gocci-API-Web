<?php
/**
 * Want Model Class
 * @package    Gocci-Web
 * @version    3.0 <2015/11/19>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V2_Db_Want extends Model
{
    /**
     * @var String $_table_name
     */
    private static $_table_name = 'wants';

    /**
     * ユーザーのWant(行きたい店)取得
     * @param Int $user_id
     *
     * @return Array $result
     */
    public static function getWant($user_id)
    {
        $query = DB::select(
            'rest_id', 'restname', 'locality'
        )
        ->from(self::$_table_name)
        ->join('restaurants', 'INNER')
        ->on('want_rest_id', '=', 'rest_id')
        ->where('want_user_id', $user_id);

        $result = $query->execute()->as_array();
        return $result;
    }

    /**
     * ユーザーのレストランFlag取得
     * @param Int $user_Id
     * @param Int $post_rest_id
     *
     * @return Int $flag
     */
    public static function getFlag($user_id, $post_rest_id)
    {
        $query = DB::select(
            'want_id'
        )
        ->from(self::$_table_name)
        ->where('want_user_id', $user_id)
        ->and_where('want_rest_id', $post_rest_id);

        $result = $query->execute()->as_array();

        if ($result == true) {
            $flag = 1;
        } else {
            $flag = 0;
        }
        return $flag;
    }

    /**
     * ユーザーの行きたい店登録数取得
     * @param Int $user_id
     *
     * @return Int $num
     */
    public static function wantNum($user_id)
    {
        $query = DB::select(self::$_table_name)
        ->where('want_user_id', $user_id);

        $result = $query->execute()->as_array();
        $num = count($result);
        return $num;
    }

    /**
     * 行きたい店登録
     * @param Int $user_id
     * @param Int $want_rest_id
     *
     * @return Array $result
     */
    public static function postWant($user_id, $want_rest_id)
    {
        $query = DB::insert(self::$_table_name)
        ->set(array(
            'want_user_id' => $user_id,
            'want_rest_id' => $want_rest_id
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
    public static function postUnwant($user_id, $want_rest_id)
    {
        $query = DB::delete(self::$_table_name)
        ->where('want_user_id', $user_id)
        ->and_where('want_rest_id', $want_rest_id);

        $result = $query->execute();
        return $result;
    }
}