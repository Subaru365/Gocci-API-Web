<?php

/**
 * Feedback Model
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_V3_DB_Feedback extends Model
{
    use GocciAPI;

    private static $_table_name = 'feedbacks';
    /**
     * 追加処理
     * @param Int $user_id
     * @param String $feedback
     *
     * @return $query
     */
    public static function post_add($user_id, $feedback)
    {
        $query = DB::insert('feedbacks')
            ->set(array(
            'feedback_user_id' => $user_id,
            'feedback'         => $feedback
            ))
            ->execute();

        return $query;
    }
}