<?php


namespace ErickJMenezes\FancyHttp\Utils;


use ErickJMenezes\FancyHttp\Client;

/**
 * Class ClassGenerator
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Utils
 * @template T
 */
class ClassGenerator
{
    protected string $generatedCode;

    public function __construct(
        protected \ReflectionClass $interface
    )
    {
        $methods = join('', $this->generateMethods());
        $this->generatedCode = "return new class(\$client) implements \\{$this->interface->getName()}{public function __construct(protected \$client){}{$methods}};";
    }

    /**
     * @param \ErickJMenezes\FancyHttp\Client $client<T>
     * @return T
     */
    public function make(Client $client)
    {
        return eval($this->generatedCode);
    }

    /**
     * Generates an array of methods.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function generateMethods(): array
    {
        $methods = [];
        foreach ($this->interface->getMethods() as $method) {
            if ($method->isStatic()) {
                throw new \Exception("Static methods are not allowed in client interface.");
            }
            $returnType = $method->hasReturnType() ? $method->getReturnType()->getName() : '';
            $args = [];
            foreach ($method->getParameters() as $parameter) {
                $argsType = $parameter->hasType() ? $parameter->getType().' ' : '';
                $argsType = $argsType !== '' && $this->classOrInterfaceExists("\\{$argsType}") ? "\\{$argsType}" : $argsType;
                $argName = $parameter->getName();
                $variadic = $parameter->isVariadic() ? '...' : '';
                $defaultValue = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : $this;
                if ($defaultValue instanceof $this) {
                    $defaultValue = '';
                } else {
                    $defaultValue = '=' . (is_string($defaultValue) ? "\"{$defaultValue}\"" : var_export($defaultValue, true));
                }
                $args[] = "{$argsType}{$variadic}\${$argName}{$defaultValue}";
            }
            $args = join(',', $args);
            $shouldReturn = $returnType !== 'void' ? 'return ' : '';
            $returnType = $returnType !== '' && $this->classOrInterfaceExists("\\{$returnType}") ? "\\{$returnType}" : $returnType;
            $returnType = $method->hasReturnType() ? ': ' . $returnType : $returnType;
            $methods[] = "public function {$method->getName()}({$args}){$returnType} {{$shouldReturn}\$this->client->{$method->getName()}(...func_get_args());}";
        }
        return $methods;
    }

    private function getClassName($classname): string
    {
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }

    private function classOrInterfaceExists(string $value): bool
    {
        return $value !== '\\' && class_exists($value, true) || interface_exists($value, true);
    }
}