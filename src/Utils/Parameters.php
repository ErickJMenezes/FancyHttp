<?php


namespace ErickJMenezes\FancyHttp\Utils;


use ErickJMenezes\FancyHttp\Attributes\Body;
use ErickJMenezes\FancyHttp\Attributes\FormParams;
use ErickJMenezes\FancyHttp\Attributes\HeaderParam;
use ErickJMenezes\FancyHttp\Attributes\Headers;
use ErickJMenezes\FancyHttp\Attributes\Multipart;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Attributes\QueryParams;
use Exception;
use InvalidArgumentException;
use ReflectionParameter;

/**
 * Class Parameters
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Utils
 */
class Parameters
{
    protected array $argsNameMap = [];
    protected array $argsPositionMap = [];

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
                    throw new InvalidArgumentException("Required argument {$parameter->name} is missing.")
                );
        }
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
        array_walk($params, function (ReflectionParameter $parameter) use (&$data) {
            $value = $this->getByName($parameter->getName());
            $data += $value;
        });
        return $data;
    }

    /**
     * @param class-string<TAttr> $attribute
     * @return array<int,ReflectionParameter>
     * @template TAttr of \ErickJMenezes\FancyHttp\Attributes\AbstractParameterAttribute
     */
    public function getWhereHasAttribute(string $attribute): array
    {
        $parameters = array_values(array_filter(
            $this->reflectionParameters,
            fn(ReflectionParameter $param) => !empty($param->getAttributes($attribute))
        ));
        $this->checkAttributeExpectations($parameters, $attribute);
        return $parameters;
    }

    /**
     * @param array<\ReflectionParameter> $params
     * @param class-string<TAttr>         $attribute
     * @return void
     * @template TAttr of \ErickJMenezes\FancyHttp\Attributes\AbstractParameterAttribute
     */
    protected function checkAttributeExpectations(array $params, string $attribute): void
    {
        foreach ($params as $parameter)
            /**
             * @var \ReflectionAttribute<TAttr> $reflectionAttribute
             * @noinspection PhpRedundantVariableDocTypeInspection
             */
            foreach ($parameter->getAttributes($attribute) as $reflectionAttribute)
                $reflectionAttribute->newInstance()
                    ->check($this->getByName($parameter->getName()));
    }

    public function getByName(string $name): mixed
    {
        return $this->argsNameMap[$name];
    }

    public function getHeaderParams(): array
    {
        $headers = $this->getAllArrayParamsOfAttributeType(Headers::class);
        $headerParams = $this->getWhereHasAttribute(HeaderParam::class);
        foreach ($headerParams as $headerParam) {
            /** @var HeaderParam $attribute */
            $attribute = $headerParam->getAttributes(HeaderParam::class)[0]->newInstance();
            $headers[$attribute->headerName] = $this->getByName($headerParam->getName());
        }
        return $headers;
    }

    public function getFormParams(): array
    {
        return $this->getAllArrayParamsOfAttributeType(FormParams::class);
    }

    public function getMultipartParams(): array
    {
        return $this->getAllArrayParamsOfAttributeType(Multipart::class);
    }

    /**
     * @throws \Exception
     */
    public function getBodyParam(): array
    {
        $parametersWithBody = $this->getWhereHasAttribute(Body::class);
        switch (count($parametersWithBody)) {
            case 0:
                return [Body::BODY, null];
            case 1:
                $parameter = $parametersWithBody[array_key_first($parametersWithBody)];
                $bodyParam = $parameter->getAttributes(Body::class)[0];
                $bodyType = $bodyParam->newInstance()->type;
                $body = $this->getAllArrayParamsOfAttributeType(Body::class);
                return [$bodyType, $body];
            default:
                throw new Exception("Only one body param are allowed.");
        }
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
                throw new Exception("The path parameter \"{$pathPlaceholder}\" is repeated.");
            } elseif ($count === 0) {
                throw new Exception("The argument \"{$pathParameter->getName()}\" is not used by any path parameter.");
            }
        }
        $missing = [];
        if (preg_match('/{.*?}/', $path, $missing)) {
            [$name] = $missing;
            throw new Exception("The path parameter \"{$name}\" has no replacement");
        }
        return $path;
    }

    protected function getByIndex(int $index): mixed
    {
        return $this->argsPositionMap[$index];
    }
}