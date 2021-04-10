<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\Concerns\ExpectsString;

#[Attribute(Attribute::TARGET_PARAMETER)]
class HeaderParam implements ParameterAttribute
{
    use ExpectsString;

    public function __construct(
        public string $headerName
    )
    {
    }
}