<?php


namespace ErickJMenezes\FancyHttp\Attributes;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class HeaderParam
{
    public function __construct(
        public string $headerName
    )
    {
    }
}