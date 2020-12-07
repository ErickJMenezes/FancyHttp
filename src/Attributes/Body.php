<?php

namespace ErickJMenezes\FancyHttp\Attributes;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Body
{
    public const TYPE_RAW = 0;
    public const TYPE_JSON = 1;

    public function __construct(
        public int $type = self::TYPE_RAW
    )
    {
    }
}