<?php
namespace Gocci;

use Gocci\Request;
use Gocci\Response;

class Object
{
    const VERSION = '3.0.0';

    private $request;
    private $response;

    public function getRequest() { return $this->request; }
    public function getHttpResponse() { return $this->request; }
    public function getError() { return $this->error; }

    public function __construct(Request $request)
    {
	$this->request = $request;
    }

    public function get(array $options = [])
    {
	$response = new Response();
        try {

        } catch (Exception $e) {

        }
	$response->setObject($this);
	return $response;
    }

    public function post(array $options = [])
    {
	$response = new Response();
	try {
      
        } catch (Exception $e) {

        }
	$response->setObject($this);
	return $response;
    }
}
