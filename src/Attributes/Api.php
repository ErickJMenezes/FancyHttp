<?php


namespace ErickJMenezes\Http\Attributes;

/**
 * Class Api
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\Http\Attributes
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