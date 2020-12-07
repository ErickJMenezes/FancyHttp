<?php

namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Head
{
    public function __construct(
        public string $path,
        public array $headers = []
    )
    {
    }
}