<?php


namespace FancyHttp\Attributes;

/**
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Attributes
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