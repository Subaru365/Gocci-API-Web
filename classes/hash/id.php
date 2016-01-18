<?php
/**
 * post_idをHashします
 * 
 * example: $hash_id = Hash_Id::video_hash($post_id);
 *
 * @params string $post_id
 * @return $hash_id 
 */
class Hash_Id
{
    public static function video_hash($post_id)
    {
        $hash_id = `/usr/local/bin/inase-hash/inasehash {$post_id}`;
        $hash_id = rtrim($hash_id);
        return $hash_id;
    }

    // user_hashからuser_idに変換するメソッド
    public static function get_user_hash($user_hash)
    {
        $user_id = `/usr/local/bin/inase-hash/inasehash -d {$user_hash}`;
        Controller_V1_Check::check_user_id_exists($user_id);
        return $user_id;
    }

    public static function create_user_hash($user_id)
    {
        $user_hash = `/usr/local/bin/inase-hash/inasehash {$user_id}`;
        $user_hash = rtrim($user_hash);
        return $user_hash;
    }


}

