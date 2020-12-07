<?php

namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Get
{
    public function __construct(
        public string $path,
        public array $headers = []
    )
    {
    }
}