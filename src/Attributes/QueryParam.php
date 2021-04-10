<?php


namespace ErickJMenezes\FancyHttp\Attributes;


use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\Concerns\ExpectsStringOrInt;

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