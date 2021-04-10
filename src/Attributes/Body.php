<?php

namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\Concerns\ExpectsString;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body implements ParameterAttribute
{
    use ExpectsString;
}