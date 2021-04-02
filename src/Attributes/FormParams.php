<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use ErickJMenezes\FancyHttp\Traits\ExpectsArray;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class FormParams extends AbstractParameterAttribute
{
    use ExpectsArray;
}