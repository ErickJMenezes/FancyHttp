<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use ErickJMenezes\FancyHttp\Traits\ExpectsString;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class HeaderParam extends AbstractParameterAttribute
{
    use ExpectsString;

    public function __construct(
        public string $headerName
    )
    {
    }
}