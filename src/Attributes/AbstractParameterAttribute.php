<?php


namespace ErickJMenezes\FancyHttp\Attributes;

/**
 * Class AbstractParameterAttribute
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
abstract class AbstractParameterAttribute
{
    /**
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    abstract public function check(mixed $value): void;
}