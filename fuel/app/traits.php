<?php
/**
 * Singleton trait
 * [How to use the Trait in FuelPHP]
 *
 * 1. fuel/app/traints.php => This File.
 * 2. Keep to read the trait to FuelPHP
 *
 * bootstrap.php
 *    + // Load trait
 *    + \Fuel::load(APPPATH.DS.'traits.php');
 *
 * 3. Use declare in a clas that you want to use the Singleto(trait)
 *
 * @package    Gocci-Web
 * @version    2.0 - 2.5 <2015/11/18>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

/**
 * クラスのインスタンスが1つしか生成されないことを保証します。
 *
 */

trait Singleton {
   /**
    * @var $instance
    */
    private static $instance = [];


    // private/protected -> Access level to Singleton::__construct() must be public (as in class Fuel\Core\Controller)
    // privateにすると、他のクラスからnewできない
    private function __construct() {
        
    }

   /**
    *
    */
    // protected final function __clone() {}

   /**
    *
    */
    // private function __wakeup() {}

    /**
     * @return String $instance
     */
    public static function getInstance() {
        $class = get_called_class();
        if ( isset(self::$instance[$class])) {
            self::$instance[$class] = new self;
        }
        return self::$instance;
    }

    /**
     * @param String $instance
     */
    public static function setInstance($instance) {
        $this->instance = $instance;
    }
}