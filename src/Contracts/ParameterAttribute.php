<?php


namespace ErickJMenezes\FancyHttp\Contracts;

/**
 * Class AbstractParameterAttribute
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
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