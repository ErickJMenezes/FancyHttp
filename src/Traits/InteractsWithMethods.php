<?php


namespace ErickJMenezes\FancyHttp\Traits;

use BadMethodCallException;
use ErickJMenezes\FancyHttp\Attributes\AbstractHttpMethod;
use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
use ErickJMenezes\FancyHttp\Attributes\ReturnsMappedList;
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use ErickJMenezes\FancyHttp\Attributes\Unwrap;
use ErickJMenezes\FancyHttp\Client;
use ErickJMenezes\FancyHttp\Lib\AMProxy;
use ErickJMenezes\FancyHttp\Lib\Implementer;
use ErickJMenezes\FancyHttp\Lib\Parameters;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Trait InteractsWithMethods
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Traits\Concerns
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
     * @param array<mixed>            $data
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
     * @param array<array<mixed>>     $data
     * @return array<IType>
     * @throws \Exception
     * @template IType of object
     */
    protected function createProxies(ReflectionClass $interface, array $data): array
    {
        $map = $interface->getAttributes(AutoMapped::class)[0]->newInstance()->map;
        $implementer = new Implementer($interface);
        $list = [];
        foreach ($data as $item) $list[] = $implementer->make(new AMProxy($item, $map));
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
     * @param class-string<I> $interface
     * @return bool
     * @template I of object
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
        $response = json_decode($response->getBody()->getContents(), true);
        if ($this->mustUnwrap()) return $response[$this->getWrapperProperty()];
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
}