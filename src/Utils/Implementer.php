<?php


namespace ErickJMenezes\FancyHttp\Utils;


/**
 * Class ClassGenerator
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp\Utils
 * @template T
 */
class Implementer
{
    protected \Closure $factory;

    /**
     * ClassGenerator constructor.
     *
     * @param \ReflectionClass<T> $interface
     * @throws \ReflectionException
     */
    public function __construct(protected \ReflectionClass $interface)
    {
        $this->generateAnonymousImplementationFactory();
    }

    /**
     * @param $parent
     * @return T
     */
    public function make($parent)
    {
        return $this->factory->call($this, $parent);
    }

    protected function generateAnonymousImplementationFactory(): void
    {
        $this->factory = eval(sprintf('return function ($parent) {
            return new class ($parent) implements %s {
                public function __construct(protected $parent) {}
                protected function callParent($method, $arguments) {return $this->parent->{$method}(...$arguments);}
                %s
            };
        };',
            "\\{$this->interface->getName()}",
            $this->generateMethods()
        ));
    }

    /**
     * Generates the interface methods
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function generateMethods(): string
    {
        $methods = [];
        foreach ($this->interface->getMethods() as $method) {
            if ($method->isStatic()) {
                throw new \Exception("Static methods are not allowed in client interface.");
            }
            $parameterList = [];
            foreach ($method->getParameters() as $parameter) {
                $paramType = $this->getParameterType($parameter);
                $argName = $parameter->getName();
                $variadic = $parameter->isVariadic() ? '...' : '';
                $defaultValue = $this->getParameterDefaultValue($parameter);
                $parameterList[] = "{$paramType} {$variadic}\${$argName}{$defaultValue}";
            }
            $parameterList = join(',', $parameterList);
            $returnTypeName = $method->hasReturnType() ? $this->getTypeName($method->getReturnType()) : '';
            $showReturnType = $method->hasReturnType() ? ': ' . $returnTypeName : $returnTypeName;
            $returnStatement = $returnTypeName === 'void' ? '' : 'return ';
            $methods[] = "public function {$method->getName()}({$parameterList}){$showReturnType} 
            {{$returnStatement}\$this->callParent(\"{$method->getName()}\", func_get_args());}";
        }
        return join(PHP_EOL, $methods);
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return string
     * @throws \Exception
     */
    protected function getParameterType(\ReflectionParameter $parameter): string
    {
        $name = '';
        if ($parameter->hasType()) {
            $parameterType = $parameter->getType();
            $this->checkIsUnionType($parameterType);
            $name = $this->getTypeName($parameterType);
        }
        return $name;
    }

    /**
     * @param \ReflectionType|null $parameterType
     * @throws \Exception
     */
    protected function checkIsUnionType(?\ReflectionType $parameterType): void
    {
        if ($parameterType instanceof \ReflectionUnionType) {
            throw new \Exception("Union types are not allowed.");
        }
    }

    protected function getTypeName(\ReflectionType $reflectionType): string
    {
        if ($this->classOrInterfaceExists($reflectionType)) {
            return "\\{$reflectionType->getName()}";
        }
        return $reflectionType->getName();
    }

    protected function classOrInterfaceExists(\ReflectionNamedType $reflectionType): bool
    {
        $name = "\\{$reflectionType->getName()}";
        return class_exists($name, true) || interface_exists($name, true);
    }

    protected function getParameterDefaultValue(\ReflectionParameter $parameter): string
    {
        $defaultValue = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : $this;
        if ($defaultValue instanceof $this) {
            $defaultValue = '';
        } else {
            $value = is_string($defaultValue) ?
                "\"{$defaultValue}\"" :
                var_export($defaultValue, true);
            $defaultValue = "={$value}";
        }
        return $defaultValue;
    }
}