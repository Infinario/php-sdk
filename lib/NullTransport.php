<?php
namespace Infinario;

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