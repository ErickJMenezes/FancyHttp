<?php


namespace ErickJMenezes\FancyHttp\Attributes;


use Attribute;

/**
 * Attribute AutoMapped
 *
 * Use this attribute to tell the client that your interface must be considered as a
 * valid return type and he must generate an implementation for it.
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AutoMapped
{
    /**
     * AutoMapped constructor.
     *
     * @param array<string,string> $map [Optional] Use this argument to help the client to match the method you defined
     *                                  in the interface with the response. Use the "dot" notation to nested properties.
     */
    public function __construct(
        public array $map = []
    )
    {
    }
}