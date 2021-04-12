<?php

namespace FancyHttp\Attributes;

use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsStringOrInt;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PathParam implements ParameterAttribute
{
    use ExpectsStringOrInt;

    public function __construct(
        public string $name
    )
    {
    }
}