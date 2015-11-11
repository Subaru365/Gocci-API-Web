<?php
exit;
class Controller_V1_Web_Exec extends Controller_V1_Web_Base 
{
    public static function action_start() {
        // レコード数分ループする
        $test_loop_num = 36;
        $production_loop_num = 470 ;
        // testで試してみる分
        // for ($i = 584; $i<=$test_loop_num; $i++) {
	// 実際のテーブルのカラム分
        for ($i = 1; $i<=$production_loop_num; $i++) {
	    print "ループ" . $i . "番目" . "\n";
            $hash_id = `/usr/local/bin/inasehash {$i}`;
	    
            $hash_id = rtrim($hash_id);
    
            // SELECT COUNT(post_id) FROM posts WHERE post_id = 3
            // カラムが存在すれば1/しなければ0
            $result = DB::select('post_id')->from('posts')->where('post_id', '=', $i)->execute(); 
	    $cnt = $result;
           // print_R($result);	
            echo (int)$ToF = count($cnt); // 0であれば以下のifの中の処理を行う
	   
           if ($ToF !== (int)1) {
	       print "存在しなかったカラムは、" . "\n";
	       print_r($result);
	       print "\n";
	       $production_loop_num++;


	       // False（存在しないのであれば）、処理を抜ける
	       // $i++;
	       print "-----------------------" . "\n";
	       print $i . "\n";
	       print '処理を抜けます' . "\n";
	       // break;
	       continue;
           }
           // 更新処理
	   // UPDATE posts SET post_hash_id = {$post_hash_id} WHERE post_id = {$post_id};
           $query = DB::update("posts")->value("post_hash_id", $hash_id)->where("post_id", "=", $i);
           $do    = $query->execute();
	   print_R($do);
        }
	print 'DONE';
    }
}
// exit;
// self::start();
