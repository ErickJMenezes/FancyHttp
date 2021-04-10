<?php

namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\Concerns\ExpectsStringOrInt;

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