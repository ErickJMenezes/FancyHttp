<?php

namespace ErickJMenezes\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class PathParam
{
    public function __construct(
        public string $paramName
    )
    {
    }
}