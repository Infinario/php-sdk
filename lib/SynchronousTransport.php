<?php
namespace Infinario;

class SynchronousTransport implements Transport
{
    public $verifyCert = true;

    public function __construct($verifyCert = true)
    {
        $this->verifyCert = (boolean)$verifyCert;
    }

    public function postAndForget(Environment $environment, $url, $payload, $timeout = Infinario::DEFAULT_TIMEOUT)
    {
        $this->post($environment, $url, $payload, $timeout);
    }

    public function post(Environment $environment, $url, $payload, $timeout = Infinario::DEFAULT_TIMEOUT)
    {
        $ch = curl_init($url);
        if ($ch === false) {
            $environment->exception(new Exception('Failed to init curl handle'));
            return false;
        }
        $payload = json_encode($payload);
        $environment->debug('posting to ' . $url, array('body' => $payload));
        $headers = array('Content-Type:application/json');
        if (curl_setopt($ch, CURLOPT_POSTFIELDS, $payload) === false) {
            curl_close($ch);
            $environment->exception(new Exception('failed setting payload'));
        }
        if (curl_setopt($ch, CURLOPT_HTTPHEADER, $headers) === false) {
            curl_close($ch);
            $environment->exception(new Exception('failed setting headers'));
        }
        if (curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) === false) {
            curl_close($ch);
            $environment->exception(new Exception('failed setting returntransfer'));
        }
        if (curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout) === false) {
            curl_close($ch);
            $environment->exception(new Exception('failed setting timeout'));
        }
        if (!$this->verifyCert) {
            if (curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0) === false) {
                curl_close($ch);
                $environment->exception(new Exception('failed setting verifyhost'));
            }
            if (curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false) === false) {
                curl_close($ch);
                $environment->exception(new Exception('failed setting verifypeer'));
            }
        }

        $result = curl_exec($ch);
        if ($result === false) {
            $environment->exception(new Exception(curl_error($ch)));
        }

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
