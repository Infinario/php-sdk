<?php
namespace Infinario;

class Exception extends \Exception
{

}

class Environment
{
    private $_debug;

    public function __construct($debug = false)
    {
        $this->_debug = $debug;
    }

    public function debug($msg, $obj=null)
    {
        if (!$this->_debug) {
            return;
        }

        echo $msg . "\n";
        if ($obj !== null) {
            print_r($obj);
            echo "\n";
        }
    }

    public function exception(Exception $exception)
    {
        if (!$this->_debug) {
            return;
        }
        throw $exception;
    }
}

interface Transport
{
    public function check(Environment $environment);
    public function postAndForget(Environment $environment, $url, $payload);
    public function post(Environment $environment, $url, $payload);
}

class NullTransport implements Transport
{
    public function postAndForget(Environment $environment, $url, $payload)
    {

    }

    public function post(Environment $environment, $url, $payload)
    {
        return '';
    }

    public function check(Environment $environment)
    {

    }
}

class ModCurlTransport implements Transport
{
    public function postAndForget(Environment $environment, $url, $payload)
    {
        $this->post($environment, $url, $payload);
    }

    public function post(Environment $environment, $url, $payload)
    {
        $environment->debug('url', $url);
        $ch = curl_init($url);
        if ($ch === false) {
            $environment->debug('Failed to init curl handle');
            return false;
        }
        $payload = json_encode($payload);
        $environment->debug('payload', $payload);
        $headers = array('Content-Type:application/json');
        if (curl_setopt($ch, CURLOPT_POSTFIELDS, $payload) === false) {
            $environment->debug('failed setting payload');
            curl_close($ch);
            return false;
        }
        if (curl_setopt($ch, CURLOPT_HTTPHEADER, $headers) === false) {
            $environment->debug('failed setting headers');
            curl_close($ch);
            return false;
        }
        if (curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) === false) {
            $environment->debug('failed setting returntransfer');
            curl_close($ch);
            return false;
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function check(Environment $environment)
    {
        if (!function_exists('curl_init')) {
            $environment->exception(new Exception('php curl module not available'));
        }
    }
}

class SynchronousTransport extends ModCurlTransport
{
}

abstract class InfinarioClientBase
{
    protected $customer = array();
    protected $environment = null;
    protected $lastTimestamp = null;

    public function __construct(Environment $environment, $customer=null) 
    {
        $this->setCustomer($customer);
        $this->environment = $environment;
    }

    protected function setCustomer($customer=null) 
    {
        if ($customer === null) {
            $this->customer = array();
        } else if (is_string($customer) || is_numeric($customer)) {
            $this->customer = array('registered' => $customer);
        } else if (is_array($customer)) {
            $this->customer = $customer;
        } else {
            $this->environment->exception(new Exception('Customer must be either string or number or array'));
            return;
        }
    }

    protected function convertMapping($val) 
    {
        if ($val === null || count($val) == 0) {
            return new \stdClass;
        }
        return $val;
    }

    protected function getTimestamp() 
    {
        $now = microtime(true);
        if ($this->lastTimestamp !== null && $now <= $this->lastTimestamp) {
            $now = $this->lastTimestamp + 0.001;
        }
        $this->lastTimestamp = $now;
        return $now;
    }

    public function identify($customer=null, $properties=null) 
    {
        $this->setCustomer($customer);
        $properties = $this->convertMapping($properties);
        $this->update($properties);
    }

    protected abstract function doTrack($eventType, array $properties, $timestamp);

    public function track($eventType, $properties=null, $timestamp=null) 
    {
        if (!is_string($eventType)) {
            $this->environment->exception(new Exception('Event type must be string'));
            return;
        }
        if ($properties === null) {
            $properties = array();
        }
        if ($timestamp !== null && !is_numeric($timestamp)) {
            $this->environment->exception(new Exception('Timestamp must be numeric'));
            return;
        }
        if (empty($this->customer)) {
            $this->environment->exception(new Exception('Customer ID is required before tracking events'));
            return;
        }
        $this->doTrack($eventType, $properties, $timestamp);
    }
}

class Infinario extends InfinarioClientBase
{

    const DEFAULT_TARGET = 'https://api.infinario.com';

    private $_initialized = false;
    private $_transport = null;
    private $_target = Infinario::DEFAULT_TARGET;
    protected $token = null;

    /**
     *
     * @param string $token your API token
     * @param array $options An array of options (since PHP does not support named arguments):
     *                       'customer' => 'john123' // registered ID = 'john123'
     *                       'customer' => ['registered' => 'john123'] // same as above
     *                       'target' => 'https://api.infinario.com' // which API server to use
     *                       'transport' => new \Infinario\SynchronousTransport() // default transport
     *                       'transport' => new \Infinario\NullTransport() // transport that does not send anything
     *                       'debug' => false // default, suppresses throwing of exceptions
     *                       'debug' => true // raises Exceptions on errors
     * @throws Exception
     */
    public function __construct($token, array $options = array())
    {
        $debug = false;
        if (array_key_exists('debug', $options)) {
            if ($options['debug']) $debug = true;
        }

        $customer = null;
        if (array_key_exists('customer', $options)) {
            $customer = $options['customer'];
        }

        parent::__construct(new Environment($debug), $customer);

        $target = null;
        if (array_key_exists('target', $options)) {
            $target = $options['target'];
        }

        $transport = null;
        if (array_key_exists('transport', $options)) {
            $transport = $options['transport'];
            if ($transport !== null && !($transport instanceof Transport)) {
                $this->environment->exception(new Exception('\'transport\' must be an instance of Transport'));
                return;
            }
        }

        if (!is_string($token)) {
            $this->environment->exception(new Exception('API token must be string'));
            return;
        }
        $this->token = $token;

        if ($target !== null) {
            if (!is_string($target)) {
                $this->environment->exception(new Exception('Target must be string or not specified'));
                return;
            }
            if (substr($target, 0, 7) !== 'http://' && substr($target, 0, 8) !== 'https://') {
                $this->environment->exception(new Exception('Target must be start with http:// or https://'));
                return;
            }
            $this->_target = rtrim($target, '/');
        }

        if ($transport === null) {
            $transport = new SynchronousTransport();
        }
        $this->_transport = $transport;
        $this->_transport->check($this->environment);
        $this->_initialized = true;
    }

    protected function url($path) 
    {
        return $this->_target . $path;
    }

    protected function doTrack($eventType, array $properties, $timestamp) 
    {
        if (!$this->_initialized) {
            $this->environment->exception(new Exception("Trying to use uninitialized Infinario"));
            return;
        }

        $properties = $this->convertMapping($properties);
        $event = array(
            'customer_ids' => $this->customer,
            'company_id' => $this->token,
            'type' => $eventType,
            'properties' => $properties
        );
        if ($timestamp !== null) {
            $event['timestamp'] = $timestamp;
        }
        $this->_postAndForget('/crm/events', $event);
    }

    public function update($properties) 
    {
        if (!$this->_initialized) {
            $this->environment->exception(new Exception("Trying to use uninitialized Infinario"));
            return;
        }
        if (empty($this->customer)) {
            $this->environment->exception(new Exception('Customer ID is required before tracking events'));
            return;
        }

        $properties = $this->convertMapping($properties);
        $data = array(
            'ids' => $this->customer,
            'company_id' => $this->token,
            'properties' => $properties
        );
        $this->_postAndForget('/crm/customers', $data);
    }

    private function _post($path, $payload)
    {
        return $this->_transport->post($this->environment, $this->url($path), $payload);
    }

    private function _postAndForget($path, $payload)
    {
        $this->_transport->postAndForget($this->environment, $this->url($path), $payload);
    }

}
