<?php

namespace ErickJMenezes\FancyHttp\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends AbstractHttpMethod
{
    public static function method(): string
    {
        return 'GET';
    }
}