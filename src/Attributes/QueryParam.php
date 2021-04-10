<?php


namespace ErickJMenezes\FancyHttp\Attributes;


use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsString;

#[Attribute(Attribute::TARGET_PROPERTY)]
class QueryParam implements ParameterAttribute
{
    public function __construct(
        public string $key
    )
    {
    }

    use ExpectsString;
}