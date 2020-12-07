<?php


namespace ErickJMenezes\FancyHttp\Attributes;

/**
 * Class Api
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Api
{
    /**
     * Api constructor.
     *
     * @param array       $headers
     * @param string|null $baseUri
     */
    public function __construct(
        public array $headers = [],
        public ?string $baseUri = null
    )
    {
    }
}