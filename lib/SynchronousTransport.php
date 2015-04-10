<?php
namespace Infinario;

class SynchronousTransport implements Transport
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