<?php

namespace ErickJMenezes\FancyHttp\Attributes;

use GuzzleHttp\RequestOptions;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Body
{
    public const BODY = RequestOptions::BODY;
    public const JSON = RequestOptions::JSON;

    public function __construct(
        public string $type = self::BODY
    )
    {
    }
}