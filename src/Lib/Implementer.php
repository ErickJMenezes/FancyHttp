<?php


namespace FancyHttp\Lib;


use Closure;
use ReflectionClass;

/**
 * Class Implementer
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezesFancyHttp\Lib
 * @template T as object
 * @internal
 */
class Implementer
{
    /**
     * @var array<class-string, \Closure>
     */
    protected static array $cache;

    protected Closure $factory;

    /**
     * Implementer constructor.
     *
     * @param \ReflectionClass<T> $interface
     * @throws \Exception
     */
    public function __construct(protected ReflectionClass $interface)
    {
        $this->factory = static::$cache[$this->interface->getName()] ??= $this->generateFactory();
    }

    /**
     * @throws \Exception
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedInferredReturnType
     */
    protected function generateFactory(): Closure
    {
        return eval(sprintf('return function ($parent) {
            return new class ($parent) implements %s {
                public function __construct(protected $parent) {}
                public function __get(string $name) {return $this->parent->$name;}
                public function __set(string $name, $value) {$this->parent->$name = $value;}
                protected function callParent($method, $arguments) {return $this->parseReturnedValue($this->parent->{$method}(...$arguments));}
                protected function parseReturnedValue($value) {return $value === $this->parent ? $this : $value;}
                %s
            };
        };',
            '\\' . $this->interface->getName(), $this->generateMethods()
        ));
    }

    /**
     * Generates the interface methods
     *
     * @throws \Exception
     */
    protected function generateMethods(): string
    {
        $methods = '';
        foreach ($this->interface->getMethods() as $method) {
            $methods .= new MethodGenerator($method);
        }
        return $methods;
    }

    /**
     * @param object $parent
     * @return T
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public function make(object $parent): mixed
    {
        return $this->factory->call($this, $parent);
    }
}