<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Delete
{
    public function __construct(
        public string $path,
        public array $headers = []
    )
    {
    }
}