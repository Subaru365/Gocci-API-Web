<?php
/**
 * Date Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Date extends Model
{
    /**
     * TIMESTAMPから現在までの差分を求める
     * @param String $date
     *
     * @return String $date_diff
     */
    public static function get_data($date)
    {
        $datetime1 = new DateTime("$date");
        $datetime2 = new DateTime(date('Y-m-d H:i:s'));

        $interval = $datetime1->diff($datetime2);

        if ($interval->format('%y') > 0) {
            $date_diff = $interval->format('%y') . '年前';
        } elseif ($interval->format('%m') > 0) {
            $date_diff = $interval->format('%m') . 'ヶ月前';
        } elseif ($interval->format('%d') > 0) {
            $date_diff = $interval->format('%d') . '日前';
        } elseif ($interval->format('%h') > 0) {
            $date_diff = $interval->format('%h') . '時間前';
        } elseif ($interval->format('%i') > 0) {
            $date_diff = $interval->format('%i') . '分前';
        } elseif ($interval->format('%s') > 0) {
            $date_diff = $interval->format('%s') . '秒前';
        } else {
            $date_diff = 'たったいま';
        }
        return $date_diff;
    }
}
