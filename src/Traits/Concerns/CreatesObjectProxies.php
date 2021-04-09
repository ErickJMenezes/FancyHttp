<?php


namespace ErickJMenezes\FancyHttp\Traits\Concerns;

use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
use ErickJMenezes\FancyHttp\Lib\AMProxy;
use ErickJMenezes\FancyHttp\Lib\Implementer;
use ReflectionClass;

/**
 * Class CreatesObjectProxies
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Traits\Concerns
 */
trait CreatesObjectProxies
{
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
        $map = $interface->getAttributes(AutoMapped::class)[0]->newInstance()->map;
        $implementer = new Implementer($interface);
        $list = [];
        foreach ($data as $item) $list[] = $implementer->make(new AMProxy($item, $map));
        return $list;
    }

}