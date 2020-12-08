<?php


namespace ErickJMenezes\FancyHttp\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class HttpVersion
{
    /**
     * HttpVersion constructor.
     *
     * @param string|null $version Default 1.1
     */
    public function __construct(
        public ?string $version = null
    )
    {
    }
}