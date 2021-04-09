<?php


namespace ErickJMenezes\FancyHttp\Lib;


use ArrayAccess;
use BadMethodCallException;
use Iterator;
use JsonSerializable;
use Throwable;

/**
 * Class AutoMappedProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezesFancyHttp\Lib
 */
class AMProxy implements JsonSerializable, ArrayAccess, Iterator, \Stringable
{
    /**
     * @var array<string, string>
     */
    protected array $keyMap = [];

    /**
     * AMProxy constructor.
     *
     * @param array                $data
     * @param array<string,string> $map
     */
    public function __construct(
        protected array $data,
        protected array $map
    )
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
        if (str_starts_with($name, 'get')) {
            if (isset($this->map[$name])) return $this->dataGet($this->map[$name]);
            $sanitizedName = substr($this->sanitizeKey($name), 3);
            return $this->data[$this->keyMap[$sanitizedName]];
        } elseif (str_starts_with($name, 'set')) {
            $mappedKey = str_replace('set', 'get', $name);
            if (isset($this->map[$mappedKey])) {
                $this->dataSet($this->map[$mappedKey], $arguments[0]);
            } else {
                $sanitizedName = substr($this->sanitizeKey($name), 3);
                $this->data[$this->keyMap[$sanitizedName]] = $arguments[0];
            }
        } else {
            throw new BadMethodCallException("The method {$name} is not legal for AutoMapped interfaces.");
        }
    }

    public function dataGet(string $path): mixed
    {
        $propNames = explode('.', $path);
        $nested = $this->data;
        foreach ($propNames as $propName) {
            $nested = $nested[$propName] ?? trigger_error(
                    "The property path {$path} is invalid. The nested property {$propName} doesn't exists in the data set.",
                    E_USER_ERROR
                );
        }
        return $nested;
    }

    public function dataSet(string $path, mixed $value): void
    {
        $propNames = explode('.', $path);
        $paths = array_slice($propNames, 0, -1);
        $nested = &$this->data;
        foreach ($paths as $propName) {
            if (is_array($nested[$propName])) $nested = &$nested[$propName];
            else trigger_error(
                "The property path {$path} is invalid. The nested property {$propName} doesn't exists in the data set.",
                E_USER_ERROR
            );
        }
        $target = array_slice($propNames, -1)[0];
        $nested[$target] = $value;
    }

    public function __get(string $name)
    {
        return $this->dataGet($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->dataSet($name, $value);
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

    public function __toString()
    {
        return json_encode($this);
    }
}
