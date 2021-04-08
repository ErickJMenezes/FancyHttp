<?php


namespace ErickJMenezes\FancyHttp\Utils;

use ArrayObject;
use BadMethodCallException;
use ErickJMenezes\FancyHttp\Attributes\AbstractHttpMethod;
use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
use ErickJMenezes\FancyHttp\Attributes\Delete;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\Head;
use ErickJMenezes\FancyHttp\Attributes\Patch;
use ErickJMenezes\FancyHttp\Attributes\Post;
use ErickJMenezes\FancyHttp\Attributes\Put;
use ErickJMenezes\FancyHttp\Attributes\ReturnsMappedList;
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use ErickJMenezes\FancyHttp\Castable;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * Class Method
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp\Utils
 * @template T
 */
class Method
{
    /**
     * @var array<class-string<AbstractHttpMethod>>
     */
    protected static array $verbs = [Get::class, Post::class, Put::class, Patch::class, Head::class, Delete::class];
    protected ?ResponseInterface $lastResponse = null;
    protected string $returnType;
    protected AbstractHttpMethod $verb;

    /**
     * Method constructor.
     *
     * @param \ReflectionMethod                         $method
     * @param \ErickJMenezes\FancyHttp\Utils\Parameters $parameters
     */
    public function __construct(
        protected ReflectionMethod $method,
        protected Parameters $parameters
    )
    {
        $this->loadRequirements();
    }

    protected function loadRequirements(): void
    {
        $this->loadVerb();
        $this->returnType = $this->method->getReturnType()?->getName() ?? 'mixed';
    }

    protected function loadVerb(): void
    {
        foreach (self::$verbs as $verb) {
            $attributes = $this->method->getAttributes($verb);
            if (!empty($attributes)) {
                $this->verb = $attributes[0]->newInstance();
                return;
            }
        }
        throw new BadMethodCallException("Http method attribute is missing from method {$this->getName()}");
    }

    protected function getName(): string
    {
        return $this->method->getName();
    }

    /**
     * @param \GuzzleHttp\ClientInterface $client
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function call(ClientInterface $client): mixed
    {
        return $this->castResponse($this->lastResponse = $client->request(
            $this->verb::method(),
            $this->parameters->parsePath($this->verb->path),
            $this->getOptions()
        ));
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return mixed
     * @throws \Exception
     */
    protected function castResponse(ResponseInterface $response): mixed
    {
        if ($this->isReturnTypeCastable()) {
            return $this->returnType::castResponse($response);
        } elseif ($modelInterface = $this->isReturnTypeAutoMapped()) {
            return self::createProxy($modelInterface, $this->decodeResponse($response));
        } elseif ($modelInterface = $this->methodReturnsAutoMappedList()) {
            $data = self::createProxies($modelInterface, $this->decodeResponse($response));
            return $this->returnType === ArrayObject::class ? new ArrayObject($data) : $data;
        }

        return match ($this->returnType) {
            'array' => $this->decodeResponse($response),
            'void', 'null' => null,
            'bool' => true,
            'string' => $response->getBody()->getContents(),
            'int', 'float', 'double' => $response->getStatusCode(),
            'object', ArrayObject::class => new ArrayObject($this->decodeResponse($response), ArrayObject::ARRAY_AS_PROPS),
            ResponseInterface::class, Response::class, 'mixed' => $response,
            default => throw new RuntimeException("{$this->returnType} is not a valid return type.")
        };
    }

    protected function isReturnTypeCastable(): bool
    {
        try {
            $returnType = new ReflectionClass($this->returnType);
            return $returnType->implementsInterface(Castable::class);
        } catch (ReflectionException) {
            return false;
        }
    }

    protected function isReturnTypeAutoMapped(): false|ReflectionClass
    {
        return $this->isAnAutoMappedInterface($this->returnType);
    }

    /**
     * @param class-string|mixed $value
     * @return false|\ReflectionClass
     */
    protected function isAnAutoMappedInterface(mixed $value): false|ReflectionClass
    {
        try {
            $reflection = new ReflectionClass($value);
            if (
                $reflection->isInterface() &&
                !empty($reflection->getAttributes(AutoMapped::class))
            )
                return $reflection;
        } catch (ReflectionException) {
        }
        return false;
    }

    /**
     * @param \ReflectionClass<IType> $interface
     * @param array<string, mixed>    $data
     * @return IType
     * @throws \Exception
     * @template IType of object
     */
    protected static function createProxy(ReflectionClass $interface, array $data): mixed
    {
        return self::createProxies($interface, [$data])[0];
    }

    /**
     * @param \ReflectionClass<IType>     $interface
     * @param array<array<string, mixed>> $data
     * @return array<IType>
     * @throws \Exception
     * @template IType of object
     */
    protected static function createProxies(ReflectionClass $interface, array $data): array
    {
        $implementer = new Implementer($interface);
        $list = [];
        foreach ($data as $item) $list[] = $implementer->make(new AMProxy($item));
        return $list;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        return (array)json_decode($response->getBody()->getContents(), true);
    }

    protected function methodReturnsAutoMappedList(): ReflectionClass|false
    {
        if ($attribute = $this->getAttribute(ReturnsMappedList::class)) {
            return $this->isAnAutoMappedInterface($attribute->getArguments()[0]);
        }
        return false;
    }

    /**
     * @param class-string $name
     * @return \ReflectionAttribute|null
     */
    protected function getAttribute(string $name): ?ReflectionAttribute
    {
        return $this->method->getAttributes($name)[0] ?? null;
    }

    protected function getOptions(): array
    {
        [$bodyType, $bodyContents] = $this->parameters->getBodyParam();
        return array_filter([
                RequestOptions::HEADERS => $this->parameters->getHeaderParams() + $this->verb->headers,
                $bodyType => $bodyContents,
                RequestOptions::QUERY => $this->parameters->getQueryParameters(),
                RequestOptions::FORM_PARAMS => $this->parameters->getFormParams(),
                RequestOptions::MULTIPART => $this->parameters->getMultipartParams(),
                RequestOptions::VERSION => $this->verb->httpVersion,
            ]) + [RequestOptions::HTTP_ERRORS => !$this->isSuppressed()];
    }

    protected function isSuppressed(): bool
    {
        return !is_null($this->getAttribute(Suppress::class));
    }

    public function getLastGuzzleResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }
}