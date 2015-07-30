<?php
class Model_Block extends Model
{
     protected static $_table_name = 'blocks';

     //投稿を違反報告
     public static function post_block($user_id, $post_id)
     {
     	$query = DB::insert('blocks')
			->set(array(
				'block_user_id' => "$user_id",
				'block_post_id' => "$post_id"
			));

          $result = $query->execute();

          return $result;
     }
}
