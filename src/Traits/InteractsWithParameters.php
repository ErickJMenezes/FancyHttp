<?php


namespace ErickJMenezes\FancyHttp\Traits;

use Exception;
use InvalidArgumentException;
use ReflectionParameter;
use WeakMap;

/**
 * Trait InteractsWithParameters
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Traits\Concerns
 * @internal
 */
trait InteractsWithParameters
{
    use InteractsWithAttributes;

    /**
     * @var \WeakMap<\ReflectionParameter,mixed>
     */
    protected WeakMap $parameterMap;

    /**
     * @var array<ReflectionParameter>
     */
    protected array $reflectionParameters;

    protected array $arguments;

    /**
     * @param class-string<T> $attribute
     * @param mixed|null      $default
     * @return mixed|R
     * @template T of \ErickJMenezes\FancyHttp\Contracts\ParameterAttribute
     * @template R
     * @throws \Exception
     */
    protected function valueFor(string $attribute, mixed $default = null): mixed
    {
        $data = $this->valuesFor($attribute);
        if (count($data) > 1) throw new Exception("Only one attribute of type {$attribute} are allowed");
        return $data[0] ?? $default;
    }

    /**
     * @param class-string<T> $attribute
     * @return array<int,mixed>
     * @template T of \ErickJMenezes\FancyHttp\Contracts\ParameterAttribute
     */
    protected function valuesFor(string $attribute): array
    {
        $data = [];
        foreach ($this->reflectionParameters as $reflectionParameter) {
            if (!$this->hasAttribute($reflectionParameter, $attribute)) continue;
            $instance = $this->getAttributeInstance($reflectionParameter, $attribute);
            $value = $this->get($reflectionParameter);
            $instance->check($value);
            $data[] = $value;
        }
        return $data;
    }

    /**
     * @param class-string<T> $listTypeAttribute
     * @param class-string<T> $singleTypeAttribute
     * @return array
     * @throws \Exception
     * @template T of \ErickJMenezes\FancyHttp\Contracts\ParameterAttribute
     */
    protected function valuesForPair(string $listTypeAttribute, string $singleTypeAttribute): array
    {
        $group = $this->valueFor($listTypeAttribute, []);
        foreach ($this->withAttributes($singleTypeAttribute) as $parameter) {
            $key = $parameter->getAttributes($singleTypeAttribute)[0]->getArguments()[0];
            $group[$key] = $this->get($parameter);
        }
        return $group;
    }

    /**
     * @param class-string<T> $attribute
     * @return array<ReflectionParameter>
     * @template T of \ErickJMenezes\FancyHttp\Contracts\ParameterAttribute
     */
    protected function withAttributes(string $attribute): array
    {
        return array_values(array_filter(
            $this->reflectionParameters,
            fn(ReflectionParameter $parameter) => $this->hasAttribute($parameter, $attribute)
        ));
    }

    protected function loadParameterMap(): void
    {
        $this->parameterMap = new WeakMap();
        foreach ($this->reflectionParameters as $parameter) {
            $this->parameterMap[$parameter] =
                // Load by name or by position
                $this->arguments[$parameter->getName()] ??
                $this->arguments[$parameter->getPosition()] ??
                // If the parameter is not available by name or by position
                // we will try to get the default value. If the default value is not available,
                // we will throw an exception.
                (
                $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue() :
                    throw new InvalidArgumentException("Required argument {$parameter->name} is missing.")
                );
        }
    }

    protected function get(ReflectionParameter $parameter): mixed
    {
        /** @noinspection PhpIllegalArrayKeyTypeInspection */
        return $this->parameterMap[$parameter];
    }
}