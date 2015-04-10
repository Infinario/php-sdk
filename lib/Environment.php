<?php
namespace Infinario;

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