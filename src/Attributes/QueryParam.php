<?php


namespace FancyHttp\Attributes;


use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsStringOrInt;

#[Attribute(Attribute::TARGET_PARAMETER)]
class QueryParam implements ParameterAttribute
{
    public function __construct(
        public string $key
    )
    {
    }

    use ExpectsStringOrInt;
}