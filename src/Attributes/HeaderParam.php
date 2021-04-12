<?php


namespace FancyHttp\Attributes;

use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsString;

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