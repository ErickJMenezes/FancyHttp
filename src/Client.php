<?php


namespace ErickJMenezes\Http;


use ErickJMenezes\Http\Attributes\Api;
use ErickJMenezes\Http\Attributes\Body;
use ErickJMenezes\Http\Attributes\Delete;
use ErickJMenezes\Http\Attributes\Get;
use ErickJMenezes\Http\Attributes\Head;
use ErickJMenezes\Http\Attributes\HeaderParam;
use ErickJMenezes\Http\Attributes\Patch;
use ErickJMenezes\Http\Attributes\PathParam;
use ErickJMenezes\Http\Attributes\Post;
use ErickJMenezes\Http\Attributes\Put;
use ErickJMenezes\Http\Attributes\QueryParams;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * Class ClientProxy
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\Http
 * @template T
 */
class Client
{
    protected ClientInterface $client;

    protected \ReflectionClass $interface;

    protected \ReflectionAttribute $apiAttribute;

    protected null|\ReflectionMethod $currentMethod;

    protected null|\ReflectionAttribute $currentMethodVerbAttribute;

    protected array $verbMap = [
        Get::class => 'get',
        Post::class => 'post',
        Put::class => 'put',
        Patch::class => 'patch',
        Head::class => 'head',
        Delete::class => 'delete'
    ];

    /**
     * ClientProxy constructor.
     *
     * @param T      $interfaceClass
     * @param string $baseUri
     */
    public function __construct(protected mixed $interfaceClass, protected ?string $baseUri = null)
    {
        try {
            $this->interface = new \ReflectionClass($interfaceClass);
            if (!$this->interface->isInterface()) {
                $this->throwInvalidArgumentException("The first argument must be a YourClientInterface::class.");
            }
            $apiAttributes = $this->interface->getAttributes(Api::class);
            if (count($apiAttributes) === 0) {
                $this->throwInvalidArgumentException("Api attribute missing");
            }
            $this->apiAttribute = $apiAttributes[0];
        } catch (\ReflectionException $e) {
            $this->throwInvalidArgumentException($e->getMessage());
        }
        $this->initClient();
    }

    protected function throwInvalidArgumentException(string $message = ''): void
    {
        throw new \InvalidArgumentException($message);
    }

    protected function initClient(): void
    {
        $apiArgs = $this->apiAttribute->getArguments();

        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUri ?: $apiArgs['baseUri'] ?? $apiArgs[1] ?? '',
            'headers' => $apiArgs['headers'] ?? $apiArgs[0] ?? []
        ]);
    }

    /**
     * @param T      $interface
     * @param string $baseUri
     * @return T|static
     */
    public static function createFromInterface(string $interface, string $baseUri = null)
    {
        return new static($interface, $baseUri);
    }

    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->interfaceClass, $name)) {
            $this->throwBadMethodCallException("The method {$name} is not declared in {$this->interfaceClass}.");
        }

        $this->loadState($name);

        $arguments = $this->assertMethodSignature($arguments);

        $verb = $this->getHttpVerb();
        $path = $this->replacePathParams($arguments, $this->getPath());
        $options = $this->getRequestOptions($arguments);
        $returnType = $this->getCurrentMethodReturnType();

        $this->cleanState();

        $response = $this->client->request($verb, $path, $options);

        return $this->castResponseToMethodReturnType($returnType, $response);
    }

    /**
     * @param string|null $message
     * @throws \BadMethodCallException
     */
    protected function throwBadMethodCallException(string $message = null): void
    {
        throw new \BadMethodCallException($message);
    }

    /**
     * @param string $name
     */
    protected function loadState(string $name): void
    {
        $this->loadReflectionMethod($name);
        $this->loadVerbAttribute($name);
    }

    /**
     * @param string $method
     * @return mixed
     */
    protected function loadReflectionMethod(string $method): \ReflectionMethod
    {
        return $this->currentMethod ??= array_values(
            array_filter(
                $this->interface->getMethods(),
                fn(\ReflectionMethod $reflectionMethod) => $reflectionMethod->name === $method
            )
        )[0];
    }

    protected function loadVerbAttribute(string $method): \ReflectionAttribute
    {
        if ($attr = $this->currentMethodVerbAttribute ?? null) return $attr;

        foreach ($this->verbMap as $attributeClass => $verbName) {
            $attributes = $this->currentMethod->getAttributes($attributeClass);
            if (count($attributes) > 0) {
                return $this->currentMethodVerbAttribute = $attributes[0];
            };
        }
        $this->throwBadMethodCallException("The method {$method} does not have a required attribute");
    }

    protected function getHttpVerb(): string
    {
        return $this->verbMap[$this->currentMethodVerbAttribute->getName()];
    }

    protected function replacePathParams(array $params, string $path): string
    {
        $this->forEachParametersOfAttributeType(
            PathParam::class,
            function (\ReflectionAttribute $attribute, \ReflectionParameter $parameter, int|string $parameterIndex) use (&$path, $params) {
                [$pathParamName] = $attribute->getArguments();
                $pathParamValue = $params[$parameter->name] ?? $params[$parameterIndex];
                $path = preg_replace('/{' . $pathParamName . '}/', $pathParamValue, $path);
            }
        );
        return $path;
    }

    /**
     * Iterates through parameters of a given attribute class type.
     *
     * @param string   $attributeClass
     * @param \Closure $closure
     * @return $this
     */
    protected function forEachParametersOfAttributeType(string $attributeClass, \Closure $closure): static
    {
        foreach ($this->currentMethod->getParameters() as $reflectionParameterKey => $reflectionParameter) {
            $reflectionAttributes = $reflectionParameter->getAttributes($attributeClass);
            foreach ($reflectionAttributes as $reflectionAttributeKey => $reflectionAttribute) {
                $closure($reflectionAttribute, $reflectionParameter, $reflectionParameterKey);
            }
        }
        return $this;
    }

    protected function getPath(): string
    {
        [$path] = $this->currentMethodVerbAttribute->getArguments();
        return $path;
    }

    /**
     * @param mixed $arguments
     * @return array
     */
    protected function getRequestOptions(array $arguments): array
    {
        [$bodyType, $bodyContents] = $this->getRequestBody($arguments);

        return array_filter([
            'headers' => $this->getHeaderParams($arguments) + $this->getRequestHeaders(),
            $bodyType => $bodyContents,
            'query' => $this->getQueryParams($arguments)
        ]);
    }

    protected function getRequestBody(array $arguments): array
    {
        $body = ['body', null];

        $this->forEachParametersOfAttributeType(
            Body::class,
            function (\ReflectionAttribute $attribute, \ReflectionParameter $parameter, $parameterIndex) use ($arguments, &$body) {
                [$type] = $attribute->getArguments();
                $typeName = 'body';
                if ($type === Body::TYPE_JSON) $typeName = 'json';
                $body = [$typeName, $arguments[$parameterIndex]];
            }
        );

        return $body;
    }

    protected function getHeaderParams(array $arguments): array
    {
        $headers = [];

        $this->forEachParametersOfAttributeType(
            HeaderParam::class,
            function (\ReflectionAttribute $attribute, \ReflectionParameter $parameter, $paramIndex) use ($arguments, &$headers) {
                [$headerName] = $attribute->getArguments();
                $headers[$headerName] = $arguments[$parameter->name] ?? $arguments[$paramIndex];
            }
        );

        return $headers;
    }

    protected function getRequestHeaders(): array
    {
        $arguments = $this->currentMethodVerbAttribute->getArguments();
        return ($arguments['headers'] ?? $arguments[1] ?? []);
    }

    protected function getQueryParams(array $params): array
    {
        $query = [];

        $this->forEachParametersOfAttributeType(
            QueryParams::class,
            function (\ReflectionAttribute $attribute, \ReflectionParameter $parameter, $paramIndex) use ($params, &$query) {
                $query = $query + ($params[$parameter->name] ?? $params[$paramIndex]);
            }
        );

        return $query;
    }

    /**
     * @return string
     */
    protected function getCurrentMethodReturnType(): string
    {
        return $this->currentMethod->hasReturnType() ?
            $this->currentMethod->getReturnType()->getName() :
            'mixed';
    }

    /**
     * Set null for currentMethod and currentMethodVerbAttribute
     */
    protected function cleanState()
    {
        $this->currentMethod = null;
        $this->currentMethodVerbAttribute = null;
    }

    /**
     * @param string                              $returnType
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return bool|mixed|string|null
     */
    protected function castResponseToMethodReturnType(string $returnType, ResponseInterface $response): mixed
    {
        $stringResponse = $response->getBody()->getContents();

        return match ($returnType) {
            'array' => json_decode($stringResponse ?: '{}', true),
            'void', 'null' => null,
            'bool' => true,
            'string' => $stringResponse,
            'object' => json_decode($stringResponse ?: '{}'),
            default => $response
        };
    }

    protected function assertMethodSignature(array $args)
    {
        $typeMap = [
            "boolean" => 'bool',
            "integer" => 'int',
            "double" => 'float',
            'float' => 'float',
            "string" => 'string',
            "array" => 'array',
            "object" => 'object',
            "resource" => 'resource',
            "NULL" => 'null',
            "unknown type" => 'mixed'
        ];

        foreach ($this->currentMethod->getParameters() as $paramIndex => $reflectionParameter) {
            try {
                $param = $args[$reflectionParameter->name] ??
                    $args[$paramIndex] ??
                    ($defaultValue = $reflectionParameter->getDefaultValue());
            } catch (\ReflectionException) {
                $this->throwBadMethodCallException("The parameter {$reflectionParameter->name} doesn't have a default value.");
            }

            if (isset($defaultValue)) {
                $args[$reflectionParameter->getPosition()] = $defaultValue;
                unset($defaultValue);
                continue;
            } elseif ($reflectionParameter->allowsNull() && is_null($param))
                continue;
            elseif ($reflectionParameter->hasType()) {
                $paramType = $typeMap[gettype($param)];
                $reflectionParameterType = (string)$reflectionParameter->getType();
                $throw = fn() => $this->throwInvalidArgumentException("The parameter {$reflectionParameter->name} must be of type {$reflectionParameterType}, {$paramType} given.");

                if ($reflectionParameterType === 'mixed') continue;
                elseif ($paramType === 'object') {
                    if (!is_object($param) || !($param instanceof $reflectionParameterType)) {
                        $throw();
                    }
                } elseif ($paramType !== $reflectionParameterType) {
                    $throw();
                }
            }
        }

        return $args;
    }
}