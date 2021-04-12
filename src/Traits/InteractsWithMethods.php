<?php


namespace FancyHttp\Traits;

use BadMethodCallException;
use FancyHttp\Attributes\AbstractHttpMethod;
use FancyHttp\Attributes\Async;
use FancyHttp\Attributes\ReturnsMappedList;
use FancyHttp\Attributes\Suppress;
use FancyHttp\Attributes\Unwrap;
use FancyHttp\Client;
use FancyHttp\Lib\AMProxy;
use FancyHttp\Lib\Implementer;
use FancyHttp\Lib\Parameters;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionMethod;
use function FancyHttp\array_get;

/**
 * Trait InteractsWithMethods
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Traits\Concerns
 * @internal
 */
trait InteractsWithMethods
{
    use InteractsWithAutoMappedTypes;

    protected AbstractHttpMethod $verb;

    protected string $returnType;

    protected Parameters $parameters;

    protected ReflectionMethod $method;

    protected Client $parent;

    protected function loadVerb(): void
    {
        foreach ($this->method->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (is_a($instance, AbstractHttpMethod::class)) {
                $this->verb = $instance;
                return;
            }
        }
        throw new BadMethodCallException("The method {$this->method->getName()} has no verb attribute.");
    }

    protected function isSuppressed(): bool
    {
        return $this->hasAttribute($this->method, Suppress::class);
    }

    protected function returnAutoMapped(): false|ReflectionClass
    {
        return $this->isAutoMapped($this->returnType);
    }

    protected function returnMappedList(): bool|ReflectionClass
    {
        return $this->returnsAutoMappedList($this->method);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        $response = (array)json_decode($response->getBody()->getContents(), true);
        if ($this->mustUnwrap() && !empty($response)) return array_get($response, $this->getWrapperProperty());
        return $response;
    }

    protected function mustUnwrap(): bool
    {
        return $this->hasAttribute($this->method, Unwrap::class);
    }

    protected function getWrapperProperty(): string
    {
        return $this->getAttributeInstance($this->method, Unwrap::class)->property;
    }

    protected function isAsynchronous(): bool
    {
        return is_a($this->returnType, PromiseInterface::class, true);
    }
}