<?php


namespace ErickJMenezes\FancyHttp\Attributes;

/**
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
abstract class AbstractHttpMethod
{
    public function __construct(
        public string $path
    )
    {
    }

    abstract public static function method(): string;
}