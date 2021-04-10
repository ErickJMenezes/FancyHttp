<?php


namespace ErickJMenezes\FancyHttp\Attributes\Auth;


use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\Concerns\ExpectsStringOrInt;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Bearer implements ParameterAttribute
{
    use ExpectsStringOrInt;
}