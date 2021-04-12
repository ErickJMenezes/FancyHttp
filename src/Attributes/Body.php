<?php

namespace FancyHttp\Attributes;

use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsString;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body implements ParameterAttribute
{
    use ExpectsString;
}