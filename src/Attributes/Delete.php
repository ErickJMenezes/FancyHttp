<?php


namespace ErickJMenezes\Http\Attributes;


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