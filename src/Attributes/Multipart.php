<?php


namespace ErickJMenezes\FancyHttp\Attributes;


use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsArray;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Multipart implements ParameterAttribute
{
    use ExpectsArray;
}