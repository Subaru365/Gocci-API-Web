<?php
/**
 * Re Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_Re extends Model
{
    /**
     * コメント登録
     * @param  Int $comment_id
     * @param  Int $user_id
     */
    public static function post_data($comment_id, $user_id)
    {
      $query = DB::insert('res')
      ->set(array(
        're_comment_id' => "$comment_id",
        're_user_id'    => "$user_id"
      ))
      ->execute();
    }

    /**
     * コメント登録
     * @param  Int $comment_id
     * @return Array $re_data
     */
    public static function get_data($comment_id)
    {
      $query = DB::select('user_id', 'username')
      ->from('res')

      ->join('users', 'INNER')
      ->on('re_user_id', '=', 'user_id')

      ->where('re_comment_id', "$comment_id");

      $re_data = $query->execute()->as_array();
      return $re_data;
    }
}
