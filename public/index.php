<?php
error_reporting(-1);
ini_set('display_errors', 1);
define('DOCROOT', __DIR__.DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__.'/../fuel/app/').DIRECTORY_SEPARATOR);
define('PKGPATH', realpath(__DIR__.'/../fuel/packages/').DIRECTORY_SEPARATOR);
define('COREPATH', realpath(__DIR__.'/../fuel/core/').DIRECTORY_SEPARATOR);
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

if ( ! file_exists(COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php'))
{
		die('No composer autoloader found. Please run composer to install the FuelPHP framework dependencies first!');
}

require COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php';
class_alias('Fuel\\Core\\Autoloader', 'Autoloader');

$routerequest = function($route = null, $e = false)
{
		Request::reset_request(true);
		$route = array_key_exists($route, Router::$routes) ? Router::$routes[$route]->translation : Config::get('routes.'.$route);

		if ($route instanceof Closure)
		{
				$response = $route();

				if( ! $response instanceof Response)
				{
						$response = Response::forge($response);
				}
		}
		elseif ($e === false)
		{
				$response = Request::forge()->execute()->response();
		}
		elseif ($route)
		{
				$response = Request::forge($route, false)->execute(array($e))->response();
		}
		else
		{
				throw $e;
		}
		return $response;
};

try {
		require APPPATH.'bootstrap.php';

		$response = $routerequest();
} catch (HttpNoAccessException $e) {
		$response = $routerequest('_403_', $e);
} catch (HttpNotFoundException $e) {
		$response = $routerequest('_404_', $e);
} catch (HttpServerErrorException $e) {
		$response = $routerequest('_500_', $e);
}

$response->body((string) $response);
if (strpos($response->body(), '{exec_time}') !== false or strpos($response->body(), '{mem_usage}') !== false) {
		$bm = Profiler::app_total();
		$response->body(
		str_replace(
			array('{exec_time}', '{mem_usage}'),
			array(round($bm[0], 4), round($bm[1] / pow(1024, 2), 3)),
			$response->body()
		)
	);
}
$response->send(true);