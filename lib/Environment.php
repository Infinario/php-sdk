<?php
namespace Infinario;

class Environment
{
    private $_debug;
    private $_logger;

    public function __construct($debug = false, $logger = null)
    {
        $this->_debug = $debug;
        $this->_logger = $logger;
    }

    public function debug($msg, array $context=array())
    {
        if (!$this->_debug) {
            return;
        }

        if ($this->_logger !== null) {
            $this->_logger->debug($msg, $context);
        }
    }

    public function exception(Exception $exception)
    {
        if (!$this->_debug) {
            if ($this->_logger !== null) {
                $this->_logger->error($exception->getMessage(), array('exception' => $exception));
            }
        }
        throw $exception;
    }
}
