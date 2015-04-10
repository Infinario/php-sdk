<?php
namespace Infinario;

interface Transport
{
    public function check(Environment $environment);
    public function postAndForget(Environment $environment, $url, $payload);
    public function post(Environment $environment, $url, $payload);
}