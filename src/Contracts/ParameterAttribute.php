<?php


namespace FancyHttp\Contracts;

/**
 * Class AbstractParameterAttribute
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Attributes
 * @internal
 */
interface ParameterAttribute
{
    /**
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function check(mixed $value): void;
}