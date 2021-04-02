<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use ErickJMenezes\FancyHttp\Traits\ExpectsArray;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Headers extends AbstractParameterAttribute
{
    use ExpectsArray;
}