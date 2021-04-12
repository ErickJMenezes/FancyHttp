<?php

namespace FancyHttp\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Head extends AbstractHttpMethod
{
    public static function method(): string
    {
        return 'HEAD';
    }
}