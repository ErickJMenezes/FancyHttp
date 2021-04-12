<?php


namespace FancyHttp\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Put extends AbstractHttpMethod
{
    public static function method(): string
    {
        return 'PUT';
    }
}