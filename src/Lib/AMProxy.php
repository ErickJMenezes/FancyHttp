<?php


namespace FancyHttp\Lib;


use ArrayAccess;
use BadMethodCallException;
use FancyHttp\Attributes\MapTo;
use FancyHttp\Traits\InteractsWithAttributes;
use Exception;
use Iterator;
use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionUnionType;
use Stringable;
use function FancyHttp\array_get;
use function FancyHttp\array_set;

/**
 * Class AutoMappedProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezesFancyHttp\Lib
 * @internal
 */
class AMProxy implements JsonSerializable, ArrayAccess, Iterator, Stringable
{
    use InteractsWithAttributes;

    /**
     * @var array<class-string, array<string,string>>
     */
    protected static array $mapCache = [];
    /**
     * @var array<string, string>
     */
    protected array $keyMap = [];

    /**
     * AMProxy constructor.
     *
     * @param array            $data
     * @param \ReflectionClass $interface
     */
    public function __construct(
        protected array $data,
        protected ReflectionClass $interface
    )
    {
        $interfaceName = $this->interface->getName();
        if (isset(self::$mapCache[$interfaceName])) {
            $this->keyMap = self::$mapCache[$interfaceName];
        } else {
            foreach ($this->interface->getMethods() as $method)
                if ($method->class === $interfaceName)
                    $this->loadMethodMap($method);
            self::$mapCache[$interfaceName] = $this->keyMap;
        }
    }

    protected function loadMethodMap(ReflectionMethod $method): void
    {
        if ($this->hasAttribute($method, MapTo::class)) {
            $name = $this->getAttributeInstance($method, MapTo::class)->property;
            $this->keyMap[$method->getName()] = $name;
            return;
        }
        throw new BadMethodCallException("The method \"{$method}\" has no property map.");
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed|void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        $mappedName = $this->keyMap[$name];
        if (count($arguments) === 0) {
            return $this->get($mappedName);
        } else {
            $this->set($mappedName, $arguments[0]);
            $method = $this->interface->getMethod($name);
            if (!$method->hasReturnType()) return $this;
            $returnType = $method->getReturnType();
            $possibleReturnTypes = ['static', 'self', $method->getName()];
            if (
                $returnType instanceof ReflectionUnionType ||
                !in_array($returnType->getName(), array_merge($possibleReturnTypes, ['void']))
            ) {
                $this->throwIllegalReturnTypeException($method);
            } elseif ($returnType->getName() === 'void') return;
            else return $this;
        }
    }

    public function get(string $path): mixed
    {
        return array_get($this->data, $path);
    }

    public function set(string $path, mixed $value): void
    {
        array_set($this->data, $path, $value);
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (array)$this->jsonSerialize();
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function next()
    {
        next($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function valid()
    {
        return !empty($this->current());
    }

    public function current()
    {
        return current($this->data);
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @param int $options
     * @return string
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function toJson($options = 0)
    {
        return json_encode($this, $options);
    }

    /**
     * @param \ReflectionMethod $method
     * @throws \Exception
     */
    private function throwIllegalReturnTypeException(ReflectionMethod $method): void
    {
        throw new Exception("Ilegal return type for method 
              {$this->interface->getShortName()}::{$method->getName()}(). Setters can only return self reference or void.");
    }
}
