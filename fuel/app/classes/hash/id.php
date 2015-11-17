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
	$hash_id = `/usr/local/bin/inasehash {$post_id}`;	
	$hash_id = rtrim($hash_id);
	return $hash_id;
    }
}

