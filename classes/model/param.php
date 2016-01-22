<?php
/**
 * Param Model
 * @package    Gocci-Web
 * @version    3.0 <2015/1/14>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_Param extends Model
{
  // use SingletonTraint;

  /**
   * @param String $token
   */
  public $token;

  /**
   * @param String $image
   */
  public $image;

  /*
  private function __construct($token, $image)
  {
    $this->token = $token;
    $this->image = $image;
  }
  */
  public function __construct($token, $image)
  {
    $this->token = $token;
    $this->image = $image;
  }

  public static function getToken()
  {
    return $this->token;
  }

  public static function getImage()
  {
    return $this->image;
  }

  public function setToken($token)
  {
    $this->token = $token;
  }

  public function setImage($image)
  {
    $this->image = $image;
  }

}