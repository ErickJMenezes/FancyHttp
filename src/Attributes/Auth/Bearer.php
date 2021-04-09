<?php


namespace ErickJMenezes\FancyHttp\Attributes\Auth;


use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsString;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Bearer implements ParameterAttribute
{
    use ExpectsString;
}