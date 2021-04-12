<?php


namespace FancyHttp\Attributes;

use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsArray;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FormParams implements ParameterAttribute
{
    use ExpectsArray;
}