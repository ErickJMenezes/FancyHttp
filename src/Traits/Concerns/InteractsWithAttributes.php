<?php


namespace ErickJMenezes\FancyHttp\Traits\Concerns;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Trait InteractsWithAttributes
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Traits\Concerns
 * @internal
 */
trait InteractsWithAttributes
{
    /**
     * @param \ReflectionMethod|\ReflectionParameter|\ReflectionClass $reflection
     * @param class-string                                            $name
     * @return bool
     */
    protected function hasAttribute(ReflectionMethod|ReflectionParameter|ReflectionClass $reflection, string $name): bool
    {
        return !empty($reflection->getAttributes($name));
    }

    /**
     * @param \ReflectionMethod|\ReflectionParameter|\ReflectionClass $reflection
     * @param class-string<T>                                         $name
     * @return T
     * @template T
     */
    protected function getAttributeInstance(ReflectionMethod|ReflectionParameter|ReflectionClass $reflection, string $name): object
    {
        return $this->getAttributeInstances($reflection, $name)[0];
    }

    /**
     * @param \ReflectionMethod|\ReflectionParameter|\ReflectionClass $reflection
     * @param class-string<T>                                         $name
     * @return array<T>
     * @template T
     */
    protected function getAttributeInstances(ReflectionMethod|ReflectionParameter|ReflectionClass $reflection, string $name): array
    {
        $instances = [];
        foreach ($reflection->getAttributes($name) as $attribute) {
            $instances[] = $attribute->newInstance();
        }
        return $instances;
    }
}