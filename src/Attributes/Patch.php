<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Patch
{
    public const METHOD = 'patch';

    public function __construct(
        public string $path = '',
        public array $headers = []
    )
    {
    }
}