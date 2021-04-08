<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsArray;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FormParams extends AbstractParameterAttribute
{
    use ExpectsArray;
}