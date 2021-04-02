<?php


namespace ErickJMenezes\FancyHttp\Utils;

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

/**
 * Class Method
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp\Utils
 * @template T
 */
class Method
{
    protected static array $verbs = [Get::class, Post::class, Put::class, Patch::class, Head::class, Delete::class];
    protected ResponseInterface $lastResponse;
    protected \ReflectionAttribute $httpMethodAttribute;
    protected string $path;
    protected array $headers;
    protected ?string $httpVersion;
    protected string $returnType;

    /**
     * Method constructor.
     *
     * @param \ReflectionMethod                         $method
     * @param \ErickJMenezes\FancyHttp\Utils\Parameters $parameters
     */
    public function __construct(
        protected \ReflectionMethod $method,
        protected Parameters $parameters
    )
    {
        $this->loadRequirements();
    }

    protected function loadRequirements(): void
    {
        $this->loadReturnType();
        $this->loadHttpMethodAttribute();
        $this->loadHttpMethodArguments();
    }

    protected function loadReturnType(): void
    {
        $this->returnType = $this->method->hasReturnType() ?
            $this->method->getReturnType()->getName() :
            'mixed';
    }

    protected function loadHttpMethodAttribute(): void
    {
        foreach (self::$verbs as $attributeClass) {
            $attributes = $this->method->getAttributes($attributeClass);
            if (!empty($attributes)) {
                $this->httpMethodAttribute = $attributes[0];
                return;
            };
        }
        throw new \BadMethodCallException("Http method attribute is missing from method {$this->getName()}");
    }

    protected function getName(): string
    {
        return $this->method->getName();
    }

    protected function loadHttpMethodArguments(): void
    {
        /** @var \ErickJMenezes\FancyHttp\Attributes\AbstractHttpMethod $method */
        $method = $this->httpMethodAttribute->newInstance();
        $this->headers = $method->headers;
        $this->httpVersion = $method->httpVersion;
        $this->path = $this->parameters->parsePath($method->path);
    }

    public function call(ClientInterface $client): mixed
    {
        return $this->castResponse($this->lastResponse = $client->request(
            $this->httpMethodAttribute->getName()::METHOD,
            $this->path,
            $this->getOptions()
        ));
    }

    protected function castResponse(ResponseInterface $response): mixed
    {
        $decodedResponse = fn() => json_decode($response->getBody()->getContents(), true);
        if ($this->isReturnTypeCastable())
            return $this->returnType::castResponse($response);
        elseif ($modelInterface = $this->isReturnTypeAutoMapped()) {
            return AMProxy::make($modelInterface, $decodedResponse());
        } elseif ($modelInterface = $this->methodReturnsAutoMappedList()) {
            $data = AMProxy::makeMany($modelInterface, $decodedResponse());
            return $this->returnType === \ArrayObject::class ? new \ArrayObject($data) : $data;
        }

        return match ($this->returnType) {
            'array' => $decodedResponse(),
            'void', 'null' => null,
            'bool' => true,
            'string' => $response->getBody()->getContents(),
            'int', 'float', 'double' => $response->getStatusCode(),
            'object', \ArrayObject::class => new \ArrayObject($decodedResponse(), \ArrayObject::ARRAY_AS_PROPS),
            ResponseInterface::class, Response::class, 'mixed' => $response,
            default => throw new \RuntimeException("{$this->returnType} is not a valid return type.")
        };
    }

    protected function isReturnTypeCastable(): bool
    {
        try {
            $returnType = new \ReflectionClass($this->returnType);
            return $returnType->implementsInterface(Castable::class);
        } catch (\ReflectionException) {
            return false;
        }
    }

    protected function isReturnTypeAutoMapped(): false|\ReflectionClass
    {
        return $this->isAnAutoMappedInterface($this->returnType);
    }

    protected function isAnAutoMappedInterface($value): false|\ReflectionClass
    {
        try {
            $reflection = new \ReflectionClass($value);
            if (
                $reflection->isInterface() &&
                isset($reflection->getAttributes(AutoMapped::class)[0])
            )
                return $reflection;
        } catch (\ReflectionException) {
        }
        return false;
    }

    protected function methodReturnsAutoMappedList(): \ReflectionClass|false
    {
        if ($attribute = $this->getAttribute(ReturnsMappedList::class)) {
            $arg = $attribute->getArguments()[0];
            return $this->isAnAutoMappedInterface($arg);
        }
        return false;
    }

    /**
     * @param class-string $name
     * @return \ReflectionAttribute|null
     */
    protected function getAttribute(string $name): ?\ReflectionAttribute
    {
        return $this->method->getAttributes($name)[0] ?? null;
    }

    protected function getOptions(): array
    {
        [$bodyType, $bodyContents] = $this->parameters->getBodyParam();
        return array_filter([
                RequestOptions::HEADERS => $this->parameters->getHeaderParams() + $this->headers,
                $bodyType => $bodyContents,
                RequestOptions::QUERY => $this->parameters->getQueryParameters(),
                RequestOptions::FORM_PARAMS => $this->parameters->getFormParams(),
                RequestOptions::MULTIPART => $this->parameters->getMultipartParams(),
                RequestOptions::VERSION => $this->httpVersion,
            ]) + [RequestOptions::HTTP_ERRORS => !$this->isSuppressed()];
    }

    protected function isSuppressed(): bool
    {
        return !is_null($this->getAttribute(Suppress::class));
    }

    public function getLastGuzzleResponse(): ResponseInterface
    {
        return $this->lastResponse;
    }
}