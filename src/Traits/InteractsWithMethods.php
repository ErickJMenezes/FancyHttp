<?php


namespace FancyHttp\Traits;

use BadMethodCallException;
use FancyHttp\Attributes\AbstractHttpMethod;
use FancyHttp\Attributes\Async;
use FancyHttp\Attributes\AutoMapped;
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
use ReflectionException;
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
    use InteractsWithAttributes;

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

    /**
     * @param \ReflectionClass<IType> $interface
     * @param array<string,mixed>            $data
     * @return IType
     * @throws \Exception
     * @template IType of object
     */
    protected function createProxy(ReflectionClass $interface, array $data): mixed
    {
        return self::createProxies($interface, [$data])[0];
    }

    /**
     * @param \ReflectionClass<IType> $interface
     * @param array<array<string,mixed>>     $data
     * @return array<IType>
     * @throws \Exception
     * @template IType of object
     */
    protected function createProxies(ReflectionClass $interface, array $data): array
    {
        $implementer = new Implementer($interface);
        $list = [];
        foreach ($data as $item) $list[] = $implementer->make(new AMProxy($item, $interface));
        return $list;
    }

    protected function isSuppressed(): bool
    {
        return $this->hasAttribute($this->method, Suppress::class);
    }

    protected function returnAutoMapped(): bool
    {
        return $this->isAutoMapped($this->returnType);
    }

    /**
     * @param class-string<TClassString> $interface
     * @return bool
     * @template TClassString
     */
    protected function isAutoMapped(string $interface): bool
    {
        try {
            return interface_exists($interface) &&
                $this->hasAttribute(new ReflectionClass($this->returnType), AutoMapped::class);
        } catch (ReflectionException) {
            return false;
        }
    }

    protected function returnMappedList(): bool
    {
        return $this->hasAttribute($this->method, ReturnsMappedList::class);
    }

    /**
     * @return \ReflectionClass<I>
     * @throws \ReflectionException
     * @template I of object
     */
    protected function getAutoMappedInterface(): ReflectionClass
    {
        return new ReflectionClass($this->returnType);
    }

    /**
     * @return ReflectionClass<I>
     * @throws \ReflectionException
     * @template I of object
     */
    protected function getMappedListInterface(): ReflectionClass
    {
        return new ReflectionClass($this->getAttributeInstance($this->method, ReturnsMappedList::class)->interface);
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