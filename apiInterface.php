<?php
/* base GocciAPI
 *
 * @package    Gocci-Web
 * @version    3.0 <2015/11/24>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
trait GocciAPI {
    /**
     * @var $instance
     */
    private static $instance = [];

    // private function __construct() {}

    /**
     * @return String $instance
     */
    public static function getInstance()
    {
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