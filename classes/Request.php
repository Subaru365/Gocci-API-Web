<?php
namespace Gocci;

class Request
{
    const VERSION = "3.0.0";
    const API_BASE_URL = 'https://{subdomain}.gocci.me/{version}/';
    const API_HOST = '';
    const API_URL_EXT = '.json';

    private $token = '';
    private $subdomain = 'api.web';
    private $version = 'v1';
    private $defaultOptions = ['allow_redirects' => false];

    public function __construct($subdomain, $token)
    {


    }

    public function get($url = null, array $options = [])
    {

    }

    public function post($url = null, array $options = [])
    {

    }

    public function delete($url = null, array $options = [])
    {

    }

    public function put($url = null, array $options = []) 
    {

    }
    
}
