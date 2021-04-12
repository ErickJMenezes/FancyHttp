<?php


namespace FancyHttp\Lib;


use ArrayAccess;
use ArrayObject;
use BadMethodCallException;
use Exception;
use FancyHttp\Attributes\MapTo;
use FancyHttp\Traits\InteractsWithAutoMappedTypes;
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
    use InteractsWithAutoMappedTypes;

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
     * @throws \Exception
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
                if ($method->class === $interfaceName) {
                    $this->checkMethodReturnType($method);
                    $this->loadMethodMap($method);
                }
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
        throw new BadMethodCallException("The method {$method->class}::{$method->getName()}() has no property map.");
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
        $method = $this->interface->getMethod($name);
        $returnType = $method->getReturnType()?->getName() ?? 'mixed';
        $mappedName = $this->keyMap[$name];

        if (count($arguments) === 0) {
            $data = $this->get($mappedName);
            if ($mappedInterface = $this->isAutoMapped($returnType)) {
                return $this->createProxy($mappedInterface, $data);
            } elseif ($mappedInterface = $this->returnsAutoMappedList($method)) {
                $list = $this->createProxies($mappedInterface, $data);
                return $returnType === ArrayObject::class ?
                    new ArrayObject($list, ArrayObject::ARRAY_AS_PROPS) :
                    $list;
            }
            return $data;
        } else {
            $this->set($mappedName, $arguments[0]);
            if ($returnType === 'void') return;
            else return $this;
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @throws \Exception
     */
    protected function checkMethodReturnType(ReflectionMethod $method): void
    {
        $returnType = $method->getReturnType();
        (
            $returnType instanceof ReflectionUnionType
        ) && throw new Exception("Ilegal return type for method {$this->interface->getShortName()}::{$method->getName()}().");
    }

    protected function get(string $path): mixed
    {
        return array_get($this->data, $path);
    }

    protected function set(string $path, mixed $value): void
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
}
