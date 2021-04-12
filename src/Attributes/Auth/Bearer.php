<?php


namespace FancyHttp\Attributes\Auth;


use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsStringOrInt;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Bearer implements ParameterAttribute
{
    use ExpectsStringOrInt;
}