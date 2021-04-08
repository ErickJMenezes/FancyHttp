<?php


namespace ErickJMenezes\FancyHttp\Attributes;


use Attribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsArray;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Multipart extends AbstractParameterAttribute
{
    use ExpectsArray;
}