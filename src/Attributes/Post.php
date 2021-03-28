<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Post
{
    public const METHOD = 'post';

    public function __construct(
        public string $path = '',
        public array $headers = []
    )
    {
    }
}