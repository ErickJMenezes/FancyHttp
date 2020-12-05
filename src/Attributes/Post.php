<?php


namespace ErickJMenezes\Http\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Post
{
    public function __construct(
        public string $path,
        public array $headers = []
    )
    {
    }
}