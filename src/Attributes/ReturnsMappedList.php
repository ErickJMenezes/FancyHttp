<?php


namespace ErickJMenezes\FancyHttp\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ReturnsMappedList
{
    public function __construct(
        public string $autoMappedInterface
    )
    {
    }
}