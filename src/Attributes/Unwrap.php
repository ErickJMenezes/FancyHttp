<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;

/**
 * Class Unwrap
 *
 * allows to unwrap the response data from a wrapper object to directly
 * returns it.
 *
 * <pre>
 * // In this example, the data is wrapped in a object with metadata,
 * // about the pagination.
 * $response = [
 *     'per_page' => 5,
 *     'current_page' => 3,
 *      ...
 *     'data' => [...]
 * ]
 * </pre>
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Unwrap
{
    public function __construct(
        public string $property = 'data'
    )
    {
    }
}