<?php
/**
 * Block Class 
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Block extends Model
{
     protected static $_table_name = 'blocks';

     // 投稿を違反報告
     public static function post_block($user_id, $post_id)
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
