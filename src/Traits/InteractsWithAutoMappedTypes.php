<?php


namespace FancyHttp\Traits;

use FancyHttp\Attributes\AutoMapped;
use FancyHttp\Attributes\ReturnsMappedList;
use FancyHttp\Lib\AMProxy;
use FancyHttp\Lib\Implementer;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class InteractsWithAutoMappedTypes
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Traits
 */
trait InteractsWithAutoMappedTypes
{
    use InteractsWithAttributes;

    protected static array $ignoredReturnTypes = [
        'array', 'void', 'null', 'bool', 'string', 'int', 'float', 'double', 'mixed',
        'object', \ArrayObject::class, StreamInterface::class, ResponseInterface::class,
        Response::class
    ];

    /**
     * @param class-string<TClassString> $interface
     * @return bool
     * @template TClassString
     */
    protected function isAutoMapped(string $interface): false|ReflectionClass
    {
        try {
            if (
                !in_array($interface, self::$ignoredReturnTypes) &&
                interface_exists($interface) &&
                $this->hasAttribute($reflected = new ReflectionClass($interface), AutoMapped::class)
            )
                return $reflected;
        } catch (ReflectionException) {}
        return false;
    }

    protected function returnsAutoMappedList(ReflectionMethod $method): false|ReflectionClass
    {
        if ($this->hasAttribute($method, ReturnsMappedList::class)) try {
            $mappedInterfaceName = $this->getAttributeInstance($method, ReturnsMappedList::class)->interface;
            return $this->isAutoMapped($mappedInterfaceName);
        } catch (ReflectionException) {}
        return false;
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
}