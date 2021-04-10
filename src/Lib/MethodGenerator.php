<?php


namespace ErickJMenezes\FancyHttp\Lib;

use Exception;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Stringable;

/**
 * Class MethodGenerator
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Compilers
 * @internal
 */
class MethodGenerator implements Stringable
{
    protected string $representation;

    public function __construct(protected ReflectionMethod $method)
    {
        $this->assertMethodSignature();
        $this->compile();
    }

    /**
     * @throws \Exception
     */
    protected function assertMethodSignature(): void
    {
        $this->method->isStatic()
        && throw new Exception("Static methods are not allowed, please remove the method \"{$this->method->getName()}\".");

        foreach ($this->method->getParameters() as $parameter) {
            ($parameter->isVariadic() || $parameter->isPassedByReference())
            && throw new Exception("Variadic or passed by reference parameters are forbidden. Please fix the method \"{$this->method->getName()}\".");

            $parameter->hasType() && $parameter->getType() instanceof ReflectionUnionType
            && throw new Exception("Union types are not allowed.");
        }
    }

    public function __toString(): string
    {
        return $this->representation;
    }

    protected function compile(): void
    {
        $parameterList = [];
        foreach ($this->method->getParameters() as $parameter) {
            $paramType = $this->getParameterType($parameter);
            $argName = $parameter->getName();
            $defaultValue = $this->getParameterDefaultValue($parameter);
            $parameterList[] = "{$paramType} \${$argName}{$defaultValue}";
        }
        $parameterList = join(',', $parameterList);
        $this->representation = "public function {$this->method->getName()}({$parameterList})";
        if ($this->method->hasReturnType()) {
            $returnTypeName = $this->getTypeName($this->method->getReturnType());
            $returnStatement = $returnTypeName === 'void' ? '' : 'return ';
            $this->representation .= ": {$returnTypeName} { {$returnStatement}";
        } else {
            $this->representation .= '{ return';
        }
        $this->representation .= "\$this->callParent(\"{$this->method->getName()}\", func_get_args());}";
    }

    protected function getTypeName(ReflectionNamedType $reflectionType): string
    {
        $name = $reflectionType->getName();
        return class_exists($name) || interface_exists($name) ? "\\{$name}" : $name;
    }

    protected function getParameterType(ReflectionParameter $parameter): string
    {
        $name = '';
        if ($parameter->hasType()) {
            $parameterType = $parameter->getType();
            $name = $this->getTypeName($parameterType);
        }
        return $name;
    }

    protected function getParameterDefaultValue(ReflectionParameter $parameter): string
    {
        if (!$parameter->isDefaultValueAvailable()) return '';
        if (is_string($defaultValue = $parameter->getDefaultValue())) {
            return "=\"{$defaultValue}\"";
        }
        return '=' . var_export($defaultValue, true);
    }
}