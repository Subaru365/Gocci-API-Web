<?php
/**
 * SingletonTraint
 * @package    Gocci-Web
 * @version    3.0 <2016/1/14>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
trait SingletonTraint
{
  private static $instance;

  private function __construct() {}

  public static function getInstance()
  {
    if ( !isset(self::$instance) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}