<?php
namespace Infinario;

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