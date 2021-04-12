<?php


namespace FancyHttp\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Delete extends AbstractHttpMethod
{
    public static function method(): string
    {
        return 'DELETE';
    }
}