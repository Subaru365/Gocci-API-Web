<?php
/**
 * Block Model
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_V2_DB_Block extends Model
{
    use GocciAPI;

    /**
     * @var String $_table_name
     */
    private static $_table_name = 'blocks';

    /**
     * 投稿を違反報告
     * @param Int $user_id
     * @param Int $post_id
     *
     * @return Array $result
     */
    public static function postBlock($user_id, $post_id)
    {
        $query = DB::insert('blocks')
            ->set(array(
            'block_user_id' => $user_id,
            'block_post_id' => $post_id
        ));
        $result = $query->execute();
        return $result;
     }
}