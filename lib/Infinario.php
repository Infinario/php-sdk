<?php
namespace Infinario;

class Infinario extends InfinarioClientBase
{

    const DEFAULT_TARGET = 'https://api.infinario.com';

    private $_initialized = false;
    private $_transport = null;
    private $_target = Infinario::DEFAULT_TARGET;
    protected $token = null;

    /**
     *
     * @param string $token   your API token
     * @param array  $options An array of options (since PHP does not support named arguments):
     *                        'customer' => 'john123' // registered ID = 'john123'
     *                        'customer' => ['registered' => 'john123'] // same as above
     *                        'target' => 'https://api.infinario.com' // which API server to use
     *                        'transport' => new \Infinario\SynchronousTransport() // default transport
     *                        'transport' => new \Infinario\NullTransport() // transport that does not send anything
     *                        'debug' => false // default, suppresses throwing of exceptions
     *                        'debug' => true // raises Exceptions on errors
     * @throws Exception if an error is encountered and debug option is true
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
