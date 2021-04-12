<?php


namespace FancyHttp\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class MapTo
{
    public function __construct(
        public string $property
    )
    {
    }
}