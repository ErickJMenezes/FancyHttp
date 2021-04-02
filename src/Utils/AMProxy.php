<?php


namespace ErickJMenezes\FancyHttp\Utils;


/**
 * Class AutoMappedProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp\Utils
 * @template T
 */
class AMProxy implements \JsonSerializable, \ArrayAccess, \Iterator
{
    protected array $keyMap = [];

    private function __construct(protected array $data)
    {
        foreach ($data as $key => $value)
            $this->keyMap[$this->sanitizeKey($key)] = $key;
    }

    private function sanitizeKey(string $key): string
    {
        return str_replace([' ', '-', '_'], '', strtolower($key));
    }

    /**
     * @param \ReflectionClass<T> $interface
     * @param array               $data
     * @return T
     * @throws \ReflectionException
     */
    public static function make(\ReflectionClass $interface, array $data)
    {
        return static::makeMany($interface, [$data])[0];
    }

    /**
     * @param \ReflectionClass<T> $interface
     * @param array[]             $data
     * @return array<T>
     * @throws \ReflectionException
     */
    public static function makeMany(\ReflectionClass $interface, array $data): array
    {
        $implementer = new Implementer($interface);
        $list = [];
        foreach ($data as $item) $list[] = $implementer->make(new static($item));
        return $list;
    }

    public function __call(string $name, array $arguments)
    {
        $sanitizedName = $this->sanitizeKey($name);

        if (str_starts_with($sanitizedName, 'get')) {
            return $this->get(substr($sanitizedName, 3));
        } elseif (str_starts_with($sanitizedName, 'set')) {
            $this->set(substr($sanitizedName, 3), $arguments[0]);
        } else {
            throw new \BadMethodCallException("The method {$name} is not legal for AutoMapped interfaces.");
        }
    }

    private function get(string $key)
    {
        return $this->data[$this->keyMap[$key]];
    }

    private function set(string $key, $value): void
    {
        $this->data[$this->keyMap[$key]] = $value;
    }

    public function __get(string $name)
    {
        return $this->data[$name];
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray()
    {
        return $this->jsonSerialize();
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
        try {
            $this->current();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function current()
    {
        return current($this->data);
    }

    public function rewind()
    {
        reset($this->data);
    }
}
