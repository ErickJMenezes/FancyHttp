<?php


namespace ErickJMenezes\FancyHttp\Utils;


class Parameters
{
    protected array $argsNameMap;
    protected array $argsPositionMap;

    /**
     * MethodArguments constructor.
     *
     * @param \ReflectionParameter[] $reflectionParameters
     * @param array                  $arguments
     */
    public function __construct(
        protected array $reflectionParameters,
        protected array $arguments
    )
    {
        $this->loadParameters();
    }

    private function loadParameters(): void
    {
        foreach ($this->reflectionParameters as $parameter) {
            $this->argsNameMap[$parameter->getName()] =
            $this->argsPositionMap[$parameter->getPosition()] =
                // Load by name or by position
                $this->arguments[$parameter->getName()] ??
                $this->arguments[$parameter->getPosition()] ??
                // If the parameter is not available by name or by position
                // we will try to get the default value. If the default value is not available,
                // we will throw an exception.
                (
                $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue() :
                    throw new \InvalidArgumentException("Required argument {$parameter->name} is missing.")
                );
        }
    }

    public function getByIndex(int $index): mixed
    {
        if (isset($this->argsPositionMap[$index])) {
            return $this->argsPositionMap[$index];
        }
        throw new \InvalidArgumentException("The argument index \"{$index}\" is invalid.");
    }

    public function getAllNamed(): array
    {
        return $this->argsNameMap;
    }

    public function getAllIndexed(): array
    {
        return $this->argsPositionMap;
    }

    public function forEachOfAttribute(string $attribute, callable $callback): void
    {
        foreach ($this->getByAttribute($attribute) as $name => $value)
            $callback($value, $name);
    }

    /**
     * @param string $attribute
     * @return \ArrayObject[]
     */
    public function getByAttribute(string $attribute): array
    {
        // First, we'll filter the parameters with the given attribute
        $params = array_filter(
            $this->reflectionParameters,
            fn(\ReflectionParameter $param) => !empty($param->getAttributes($attribute))
        );
        // Then we just create a new dictionary with the real parameter values.
        $paramList = [];
        foreach ($params as $param) {
            $paramList[$param->getName()] = new \ArrayObject([
                'value' => $this->getByName($param->getName()),
                'attrArgs' => $param->getAttributes($attribute)[0]->getArguments()
            ], \ArrayObject::ARRAY_AS_PROPS);
        }
        return $paramList;
    }

    public function getByName(string $name): mixed
    {
        if (isset($this->argsNameMap[$name])) {
            return $this->argsNameMap[$name];
        }
        throw new \InvalidArgumentException("The argument name \"{$name}\" is invalid.");
    }

    public function getFirstValueByAttribute(string $attribute, $fallbackValue = null): mixed
    {
        return $this->getFirstByAttribute($attribute)->value ?? $fallbackValue;
    }

    public function getFirstByAttribute(string $attribute): ?\ArrayObject
    {
        $value = $this->getByAttribute($attribute);
        if (!empty($value)) {
            $keyFirst = array_key_first($value);
            return $value[$keyFirst] ?? null;
        }
        return null;
    }
}