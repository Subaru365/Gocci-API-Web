<?php
class Model_Re extends Model
{
  //コメント登録
  public static function post_data($comment_id, $user_id)
  {
    $query = DB::insert('res')
    ->set(array(
      're_comment_id' => "$comment_id",
      're_user_id'    => "$user_id"
    ))
    ->execute();
  }


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