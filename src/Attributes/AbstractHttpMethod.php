<?php


namespace ErickJMenezes\FancyHttp\Attributes;


abstract class AbstractHttpMethod
{
    public function __construct(
        public string $path,
        public array $headers = [],
        public string $httpVersion = '1.1'
    )
    {
    }
}