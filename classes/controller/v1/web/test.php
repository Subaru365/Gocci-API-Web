<?php
// strategy Design Pattern

// 飛行のインターフェイス
interface FlyBehavior
{
    public function fly();
}

interface RunBehavior
{
    public function run();
}

// 具体的なクラス 1
class FlyHigh implements FlyBehavior
{
    public function fly()
    {
        echo '高く跳んでいる';
    }
}

// 具体的なクラス 2
class FlyLow implements FlyBehavior
{
    public function fly()
    {
        echo '低く跳んでいる';
    }
}

// 具体的なクラス 3
class RunFast implements RunBehavior
{
    public function run()
    {
        echo '飛べない';
    }
}

// 鳥類のベースクラス
abstract class Birds
{
    abstract function __construct();

    abstract function display();

    public function sleep()
    {
        echo '寝ている';
    }

    public function eat()
    {
        echo '食べている';
    }
}

// 飛ぶタイプの鳥類
abstract class FlyableBirds extends Birds implements FlyBehavior
{
    protected $flyBehavior;

    public function fly()
    {
        $this->flyBehavior->fly();
    }
}

// 走るタイプの鳥類
abstract class RunableBirds extends Birds implements RunBehavior
{
    protected $runBehavior;

    public function run()
    {
        $this->runBehavior->run();
    }
}

// 具体的な鳥のクラス(フクロウ)
class Owl extends FlyableBirds
{
    function __construct()
    {
        $this->flyBehavior = new FlyLow();
    }

    function display()
    {
        echo 'フクロウです';
    }
}

// 具体的な鳥のクラス（カラス）
class Raven extends FlyableBirds
{
    function __construct()
    {
        $this->flyBehavior = new FlyHigh();
    }

    function display()
    {
        echo 'カラスです';
    }
}

// 具体的な鳥のクラス（駝鳥）
class Ostrich extends RunableBirds
{
    function __construct()
    {
        $this->runBehavior = new RunFast();
    }

    function display()
    {
        echo '駝鳥です';
    }
}

class Controller_V1_Web_Test extends Controller
{
    // use Singleton; // 親クラス(Controoler)でconstructでインスタンス化している
    /*
    public function before()
    {
        parent::before();
    }
    */
    protected $ostrich;
    protected $raven;
    protected $owl;

    public function __construct()
    {
        $this->ostrich = new Ostrich();
        $this->raven   = new Raven();
        $this->owl     = new Owl();
    }

    public function action_index()
    {
        echo Util\Hoge::getFoo();
        echo Controller_V1_Web_Test::sample();

        $this->ostrich->run();
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
