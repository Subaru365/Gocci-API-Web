<?php
class Controller_V1_Web_Test extends Controller
{
    // use Singleton; // 親クラス(Controoler)でconstructでインスタンス化している
    /*
    public function before()
    {
        parent::before();
    }
    */

    public function action_index()
    {
        echo Util\Hoge::getFoo();
        echo Controller_V1_Web_Test::sample();
    }
    // public function sample() # これでも呼び出せる
    public static function sample()
    {
        return 'sample';
    }

    // action_をつけてメソッドを宣言した場合、staticをつけてもつけなくても、
    // echo Controller_V1_Web_Test::sample();で呼び出せない
    /*
    public static function action_sample()
    {
      return 'sample';
    }
    */
}
