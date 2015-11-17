<?php
class Controller_V1_Web_Exec extends Controller_V1_Web_Base
{
    public static function action_start() {
        // レコード数分ループする
        $production_loop_num = 493;

        for ($i = 1; $i<=$production_loop_num; $i++) {
            // sleep(10);
            $hash_id = `/usr/local/bin/inasehash {$i}`;
            $hash_id = rtrim($hash_id);

            // Example: SELECT COUNT(post_id) FROM posts WHERE post_id = 3
            // カラムが存在すれば1/しなければ0
            $result = DB::select('post_id')->from('posts')->where('post_id', '=', $i)->execute();
            $cnt = $result;
            (int)$ToF = count($cnt); // 0であれば以下のifの中の処理を行う

            if ($ToF !== (int)1) {
                $production_loop_num++;
                // False（存在しないのであれば）、処理を抜ける
                continue;
            }
            // 更新処理
            // UPDATE posts SET post_hash_id = {$post_hash_id} WHERE post_id = {$post_id};
            // sleep(10);
            $query = DB::update("posts")->value("post_hash_id", $hash_id)->where("post_id", "=", $i);
            $do    = $query->execute();
        }
        print 'DONE';
    }
}

