<?php


namespace FancyHttp\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Patch extends AbstractHttpMethod
{
    public static function method(): string
    {
        return 'PATCH';
    }
}