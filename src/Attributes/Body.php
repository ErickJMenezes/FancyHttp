<?php

namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsArray;
use GuzzleHttp\RequestOptions;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body extends AbstractParameterAttribute
{
    use ExpectsArray;

    public const BODY = RequestOptions::BODY;
    public const JSON = RequestOptions::JSON;

    public function __construct(
        public string $type = self::BODY
    )
    {
    }
}