<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;

/**
 * Class Api
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Api
{
    /**
     * Api constructor.
     *
     * @param array             $headers
     * @param string|array|null $auth
     */
    public function __construct(
        public array $headers = [],
        public null|string|array $auth = null
    )
    {
    }
}