<?php

namespace App\Messenger\Message;

class MessengerMessage
{
    private array $params;

    private string $method;


    public function __construct(array $params, string $method)
    {
        $this->params              = $params;
        $this->method              = $method;
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
