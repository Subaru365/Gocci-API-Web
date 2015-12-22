<?php
/**
 * Regx Class Api
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V3_Regx extends Model
{
    /**
     * @var Object $val
     */
    private $val;

    /**
     * @var Array $data
     */
    private $verify_data = [];

    private function __construct($data)
    {
		$this->verify_data = $data;
		$this->$val	   = Validation::forge();
    }

    public function _check()
    {
		switch (Uri::string()) {

		    case 'v3/auth/login':
				$this->set_regex_signup();
				break;

		    case 'v3/auth/logout':
				$this->set_regex_logout();
				break;

		    case 'v3/auth/pass_login':
				$this->set_regex_pass_login();
				break;

		    case 'v3/register/sign_up':
				$this->set_regex_sign_up();
				break;
		    
		    case 'v3/register/sns_sign_up':
				$this->sns_sign_up();
				break;
		
		    default:
				Model_V3_Status::ERROR_CONNECTION_FAILED();

	}
	$this->run();

    }

    // Validation Check Run
    private function run()
    {
	$val  = $this->val;
	$data = $this->verify_data;

	if ($val->run($data)) {
	    return true;
	} else {
	    // Error
	    foreach ($val->error() as $key => $value) {
		$keys[]     = $key;
		$messages[] = $value; 
	    }
	    $key     = implode(", ", $keys);
	    $message = implode(". ", $messages);

	    Controller_V3_Web_Base::output_validation_error($key, $message);
	    error_log($message);

	    return false; 

	}
    }
}
