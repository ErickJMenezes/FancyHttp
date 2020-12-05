<?php


namespace ErickJMenezes\Http;


use ErickJMenezes\Http\Attributes\Api;
use ErickJMenezes\Http\Attributes\Body;
use ErickJMenezes\Http\Attributes\Delete;
use ErickJMenezes\Http\Attributes\Get;
use ErickJMenezes\Http\Attributes\HeaderParam;
use ErickJMenezes\Http\Attributes\Patch;
use ErickJMenezes\Http\Attributes\PathParam;
use ErickJMenezes\Http\Attributes\Post;
use ErickJMenezes\Http\Attributes\Put;
use ErickJMenezes\Http\Attributes\QueryParams;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;


/**
 * Class ClientProxy
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\Http
 * @template T
 */
class Client
{
    protected null|\ReflectionMethod $currentMethod;

    protected array $verbMap = [
        Get::class => 'get',
        Post::class => 'post',
        Put::class => 'put',
        Patch::class => 'patch',
        Delete::class => 'delete'
    ];

    protected array $availableMethods = [];

    protected array $methods;

    protected ClientInterface $client;

    protected \ReflectionClass $interface;

    protected \ReflectionAttribute $api;

    /**
     * ClientProxy constructor.
     *
     * @param T      $interfaceClass
     * @param string $baseUri
     */
    private function __construct(protected mixed $interfaceClass, protected string $baseUri = '')
    {
        try {
            $this->interface = new \ReflectionClass($interfaceClass);
            if (!$this->interface->isInterface()) {
                $this->throwInvalidArgumentException("The parameter must be an api interface.");
            }
            $apiAttributes = $this->interface->getAttributes(Api::class);
            if (count($apiAttributes) === 0) {
                $this->throwInvalidArgumentException("Api attribute missing");
            }
            $this->api = $apiAttributes[0];
        } catch (\ReflectionException) {
            $this->throwInvalidArgumentException();
        }
        $this->init();
    }

    protected function throwInvalidArgumentException(string $message = ''): void
    {
        throw new \InvalidArgumentException();
    }

    protected function init(): void
    {
        [$baseUri] = $this->api->getArguments() + [$this->baseUri];
        $this->client = new GuzzleClient([
            'base_uri' => $baseUri
        ]);
        $this->methods = $this->interface->getMethods();

        foreach ($this->methods as $method) {
            $this->availableMethods[$method->name] = [];
            foreach ($method->getParameters() as $paramIndex => $param) {
                try {
                    $this->availableMethods[$method->name][$param->getPosition()] = $param->getDefaultValue();
                } catch (\Throwable) {
                }
            }
        }
    }

    /**
     * @param T      $interface
     * @param string $baseUri
     * @return T
     */
    public static function createFromInterface(string $interface, string $baseUri = '')
    {
        return new static($interface, $baseUri);
    }

    public function __call(string $name, array $arguments)
    {
        $this->availableMethods[$name] ?? $this->throwBadMethodCallException("Undefined method {$name}");

        $arguments = $arguments + $this->availableMethods[$name];

        $verb = $this->getVerb($name);
        $pathSegment = $this->replacePathSegment($arguments, $this->getPathSegment($name));
        [$bodyType, $bodyContents] = $this->getBody($arguments);

        $options = array_filter([
            'headers' => $this->getHeaderParams($arguments) + $this->getHeaders($name),
            $bodyType => $bodyContents,
            'query' => $this->getQueryParams($arguments)
        ]);

        $returnType = $this->currentMethod->getReturnType()->getName();

        $this->currentMethod = null;

        $response = $this->client->request($verb, $pathSegment, $options);

        $returnValue = match ($returnType) {
            'array', 'json' => json_decode($response->getBody()->getContents() ?? '{}', true),
            'void' => null,
            'string' => $response->getBody()->getContents(),
            'object' => json_decode($response->getBody()->getContents()),
            default => $response
        };

        if (is_null($returnValue)) return;

        return $returnValue;
    }

    protected function throwBadMethodCallException(string $message = null): void
    {
        throw new \BadMethodCallException($message);
    }

    protected function getVerb(string $method): string
    {
        $attribute = $this->getMethodAttribute($method);
        return $this->verbMap[$attribute->getName()];
    }

    protected function getMethodAttribute(string $method): \ReflectionAttribute
    {
        $m = $this->getReflectedMethodByName($method);
        return $m->getAttributes()[0];
    }

    /**
     * @param string $method
     * @return mixed
     */
    protected function getReflectedMethodByName(string $method): \ReflectionMethod
    {
        return $this->currentMethod ??= array_values(array_filter($this->methods, function (\ReflectionMethod $reflectionMethod) use ($method) {
            return $reflectionMethod->name === $method;
        }))[0];
    }

    protected function replacePathSegment(array $params, string $pathSegment): string
    {
        foreach ($this->currentMethod->getParameters() as $rParamKey => $rParam) {
            $rParamAttribute = $rParam->getAttributes(PathParam::class)[0] ?? null;
            if (!$rParamAttribute) continue;
            $paramName = $rParamAttribute->getArguments()[0];
            $paramValue = $this->get($rParam->name, $params, $rParamKey);
            $pathSegment = preg_replace('/{' . $paramName . '}/', $paramValue, $pathSegment);
        }

        return $pathSegment;
    }

    protected function get($key, array $target, $fallbackKey, $default = null)
    {
        if (array_key_exists($key, $target)) {
            return $target[$key];
        } else {
            return $target[$fallbackKey] ?? $default;
        }
    }

    protected function getPathSegment(string $method): string
    {
        $attribute = $this->getMethodAttribute($method);
        return $attribute->getArguments()[0];
    }

    protected function getBody(array $arguments): array
    {
        $rParams = $this->currentMethod->getParameters();
        foreach ($rParams as $key => $reflectionParameter) {
            $rParamAttribute = $reflectionParameter->getAttributes(Body::class)[0] ?? null;
            if (!$rParamAttribute) continue;
            [$type] = $rParamAttribute->getArguments();
            return [$type === Body::TYPE_RAW ? 'body' : 'json', $arguments[$key]];
        }
        return ['body', null];
    }

    protected function getHeaderParams(array $arguments): array
    {
        $headers = [];

        foreach ($this->currentMethod->getParameters() as $rParamKey => $rParam) {
            $rParamAttribute = $rParam->getAttributes(HeaderParam::class)[0] ?? null;
            if (!$rParamAttribute) continue;
            $headers[$rParamAttribute->getArguments()[0]] = $this->get($rParam->name, $arguments, $rParamKey);
        }

        return $headers;
    }

    protected function getHeaders(string $method): array
    {
        $attribute = $this->getMethodAttribute($method);
        return $attribute->getArguments()[2] ?? [];
    }

    protected function getQueryParams(array $params): array
    {
        $query = [];

        foreach ($this->currentMethod->getParameters() as $rParamKey => $rParam) {
            $rParamAttribute = $rParam->getAttributes(QueryParams::class)[0] ?? null;
            if (!$rParamAttribute) continue;
            $query = $query + $this->get($rParam->name, $params, $rParamKey);
        }

        return $query;
    }
}