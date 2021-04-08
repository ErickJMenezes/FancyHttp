<?php


namespace ErickJMenezes\FancyHttp\Utils;


use ArrayAccess;
use BadMethodCallException;
use Iterator;
use JsonSerializable;
use Throwable;

/**
 * Class AutoMappedProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp\Utils
 */
class AMProxy implements JsonSerializable, ArrayAccess, Iterator
{
    /**
     * @var array<string, string>
     */
    protected array $keyMap = [];

    /**
     * AMProxy constructor.
     *
     * @param array<string, mixed> $data
     */
    public function __construct(protected array $data)
    {
        foreach (array_keys($this->data) as $key)
            $this->keyMap[$this->sanitizeKey($key)] = $key;
    }

    private function sanitizeKey(string $key): string
    {
        return str_replace([' ', '-', '_'], '', strtolower($key));
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed|void
     */
    public function __call(string $name, array $arguments)
    {
        $sanitizedName = $this->sanitizeKey($name);

        if (str_starts_with($sanitizedName, 'get')) {
            return $this->get(substr($sanitizedName, 3));
        } elseif (str_starts_with($sanitizedName, 'set')) {
            $this->set(substr($sanitizedName, 3), $arguments[0]);
        } else {
            throw new BadMethodCallException("The method {$name} is not legal for AutoMapped interfaces.");
        }
    }

    private function get(string $key): mixed
    {
        return $this->data[$this->keyMap[$key]];
    }

    private function set(string $key, mixed $value): void
    {
        $this->data[$this->keyMap[$key]] = $value;
    }

    public function __get(string $name)
    {
        return $this->data[$name];
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @param int $options
     * @return string
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function toJson($options = 0)
    {
        return (string)json_encode($this->toArray(), $options);
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
        try {
            $this->current();
            return true;
        } catch (Throwable) {
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
