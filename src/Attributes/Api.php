<?php


namespace ErickJMenezes\Http\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Api
{
    public function __construct(
        public ?string $baseUri = null,
        public bool $suppressErrors = false
    )
    {
    }
}