<?php
namespace Fuel\Tasks;
use \Config as Config;
use \Arr;

class Test
{
    public function __construct() {}

    public function run()
    {
	$this->_invoke();
    }

    public function help()
    {
	echo <<< HELP
使用方法:
    php oil refine test --file=実行したいPHPunitのtestファイル
    php oil r test --file=実行したいPHPUnitのtestファイル

コマンド一覧:
    help	ヘルプの表示
    test        phpunitの実行

説明: 
    phpunitでファイル指定で実行できるようにします。(高速)

例:
    php oil r test --file=実行したいPHPunitのtestファイル
HELP;
    }

    private function _invoke()
    {
	$phpunit_autoload_path = \Config::get('oil.phpunit.autoload_path', 'PHPUnit/Autoload.php' );
	@include_once($phpunit_autoload_path);

	if ( ! class_exists('PHPUnit_Framework_TestCase'))
        {
	    throw new Exception('PHPUnit does not appear to be installed.'.PHP_EOL.PHP_EOL."\tPlease visit http://phpunit.de and install.");
        }
	
	if (file_exists(APPPATH.'phpunit.xml'))
        {
	    $phpunit_config = COREPATH.'phpunit.xml';
        }
	else 
	{
	    $phpunit_config = COREPATH.'phpunit.xml';
	}

	$phpunit_command = \Config::get('oil.phpunit.binary_path', 'phpunit');
	$command = 'cd '.DOCROOT.'; '.$phpunit_command.' -c "'.$phpunit_config.'"';

	\Cli::option('gruop') and $command .= ' --group '.\Cil::option('gruop');
	\Cli::option('exclude-group') and $command .= ' --exclude-group '.\Cli::option('exclude-group');

	// Respect the coverage-htmL option
	

	
	$return_code = 0;

	foreach (explode(';', $command) as $c)
        {
	    passthru($c, $return_code_task);
	    $return_code |= $return_code_task;
	}
	exit($return_code);
    }

}
