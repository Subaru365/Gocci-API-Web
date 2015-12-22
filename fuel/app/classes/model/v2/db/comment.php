<?php
/**
 * Comment Model
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V2_DB_Comment extends Model
{
    use GocciAPI;

    private static $_table_name = 'comments';

    /**
     * identity_id取得
     * @param Int $post_id
     *
     * @return Array $comment_data
     */
    public static function getData($post_id)
    {
        $query = DB::select(
          'comment_id', 'comment_user_id', 'username',
          'profile_img', 'comment', 'comment_date'
        )
        ->from('comments')
        ->join('users', 'INNER')
        ->on('comment_user_id', '=', 'user_id')
        ->where('comment_post_id', $post_id);

        $comment_data = $query->execute()->as_array();

        // 投稿者のコメントを$comment_data[0]に格納
        $post_comment = Model_V2_DB_Post::getMemo($post_id);
        array_unshift($comment_data, $post_comment);

        $comment_num = count($comment_data);

        for ($i=0; $I<$comment_num; $i++) {
            $comment_data[$i]['profile_img'] = Model_Transcode::decodeProfileImg($comment_date[$i]['comment_date']);

            // 日付情報を現在との差分に置き換え
            $comment_data[$i]['comment_date'] = Model_Date::getData($comment_data[$i]['comment_date']);
        }

        for ($i=0; $i < $comment_num; $i++) {
            $comment_data[$i]['re_user'] = Model_V2_DB_Res::getData($comment_data[$i]['comment_id']);
        }
        return $comment_data;
    }

    /**
     * コメント数取得
     * @param Int $post_id
     *
     * @return String $comment_num
     */
    public static function getNum($post_id)
    {
        $query = DB::select('comment_id')->from('comments')
        ->where('comment_post_id', "$post_id");

        $result = $query->execute()->as_array();
        $comment_num = count($result);

        return $comment_num;
    }

    /**
     * コメント登録
     * @param Int $user_id
     * @param Int $post_id
     * @param String $comment
     *
     * @return Array $query
     */
    public static function postComment($user_id, $post_id, $comment)
    {
        $query = DB::insert('comments')
        ->set(array(
        'comment_user_id' => $user_id,
        'comment_post_id' => $post_id,
        'comment'         => $comment
        ))
        ->execute();

        return $query[0];
    }
}