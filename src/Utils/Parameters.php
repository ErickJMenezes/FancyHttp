<?php


namespace ErickJMenezes\FancyHttp\Utils;


use ErickJMenezes\FancyHttp\Attributes\Body;
use ErickJMenezes\FancyHttp\Attributes\FormParams;
use ErickJMenezes\FancyHttp\Attributes\HeaderParam;
use ErickJMenezes\FancyHttp\Attributes\Multipart;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Attributes\QueryParams;

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

    protected function loadParameters(): void
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

    protected function getByIndex(int $index): mixed
    {
        if (isset($this->argsPositionMap[$index])) {
            return $this->argsPositionMap[$index];
        }
        throw new \InvalidArgumentException("The argument index \"{$index}\" is invalid.");
    }

    public function getQueryParameters(): array
    {
        return $this->getAllArrayParamsOfAttributeType(QueryParams::class);
    }

    /**
     * @param class-string $attribute
     * @return array
     */
    protected function getAllArrayParamsOfAttributeType(string $attribute): array
    {
        $data = [];
        $params = $this->getWhereHasAttribute($attribute);
        array_walk($params, function (\ReflectionParameter $parameter) use (&$data) {
            $value = $this->getByName($parameter->getName());
            $data += $value;
        });
        return $data;
    }

    /**
     * @param class-string $attribute
     * @return \ReflectionParameter[]
     */
    public function getWhereHasAttribute(string $attribute): array
    {
        return array_filter(
            $this->reflectionParameters,
            fn(\ReflectionParameter $param) => !empty($param->getAttributes($attribute)),

        );
    }

    public function getByName(string $name): mixed
    {
        if (isset($this->argsNameMap[$name])) {
            return $this->argsNameMap[$name];
        }
        throw new \InvalidArgumentException("The argument name \"{$name}\" is invalid.");
    }

    public function getHeaderParams(): array
    {
        return $this->getAllArrayParamsOfAttributeType(HeaderParam::class);
    }

    public function getFormParams(): array
    {
        return $this->getAllArrayParamsOfAttributeType(FormParams::class);
    }

    public function getMultipartParams(): array
    {
        return $this->getAllArrayParamsOfAttributeType(Multipart::class);
    }

    public function getBodyParam(): array
    {
        $parametersWithBody = $this->getWhereHasAttribute(Body::class);
        $count = count($parametersWithBody);
        if ($count > 1) {
            throw new \Exception("Only one body param are allowed.");
        } elseif ($count === 0) {
            return [Body::BODY, null];
        }
        $parameter = $parametersWithBody[array_key_first($parametersWithBody)];
        $bodyParam = $parameter->getAttributes(Body::class)[0];
        $bodyType = $bodyParam->newInstance()->type;
        $body = $this->getAllArrayParamsOfAttributeType(Body::class);
        return [$bodyType, $body];
    }

    public function parsePath(string $path): string
    {
        $pathParameters = $this->getWhereHasAttribute(PathParam::class);
        foreach ($pathParameters as $pathParameter) {
            $value = $this->getByName($pathParameter->getName());
            $pathPlaceholder = $pathParameter->getAttributes(PathParam::class)[0]->newInstance()->paramName;
            $count = 0;
            $path = str_replace('{' . $pathPlaceholder . '}', $value, $path, $count);
            if ($count > 1) {
                throw new \Exception("The path parameter \"{$pathPlaceholder}\" is repeated.");
            } elseif ($count === 0) {
                throw new \Exception("The argument \"{$pathParameter->getName()}\" is not used by any path parameter.");
            }
        }
        $missing = [];
        if (preg_match('/{.*?}/', $path, $missing)) {
            [$name] = $missing;
            throw new \Exception("The path parameter \"{$name}\" has no replacement");
        }
        return $path;
    }
}