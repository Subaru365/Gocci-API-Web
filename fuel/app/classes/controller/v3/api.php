<?php
/**
 * API Base class
 */
class Controller_V3_API extends Controller_Rest
{
    /**
     * The HTTP method this request was made in, either GET, POST, PUT, DELETE
     * @var $method
     */
    protected $method = '';

    /**
     * The Model requested in the URI.
     * @var $endpoint
     */
    protected $endpoint = '';

    /**
     * An optional additional descriptor about the endpoint, used for things that can not be handled by the basic methods.
     * @var verb
     */
    protected $verb = '';

    protected $args = [];

    protected $file = null;

    public function __construct()
    {
        // header('Content-Type: application/json; charset=UTF-8');
        // header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
        // header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
        // header('X-Content-Type-Options: nosniff');

        /*
        $this->args = explode('/', rtrim($this->request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method === 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Hander");
            }
        }

        switch ($this->method) {
        case 'DELETE':
        case 'POST':
            $this->request = $this->_cleanInputs($_POST);
            break;
        case 'GET':
            $this->request = $this->_cleanInputs($_GET);
            break;
        case 'PUT':
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
            break;
        default:
            $this->_response('Invalid Method', 405);
            break;
        }
        */
    //}

    public function processAPI()
    {
        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
        return $this->_response("No endpoint: $this->endpoint", 404);
    }

    private function _response($data, $status = 200)
    {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data)
    {
        $clean_input = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code)
    {
        $status = [
          200 => 'OK',
          404 => 'Not Found',
          405 => 'Method Not Allowd',
          500 => 'Internal Server Error',
        ];
        return ($status[$code])?$status[$code]:$status[500];
    }

}