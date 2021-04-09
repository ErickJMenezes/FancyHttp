<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ReturnsMappedList
{
    public function __construct(
        public string $interface
    )
    {
    }
}