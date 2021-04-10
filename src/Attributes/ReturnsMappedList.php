<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;

/**
 * Class ReturnsMappedList
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 * @template T of object
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ReturnsMappedList
{
    /**
     * ReturnsMappedList constructor.
     *
     * @param class-string<T> $interface
     */
    public function __construct(
        public string $interface
    )
    {
    }
}