<?php


namespace ErickJMenezes\FancyHttp\Lib;


use ArrayAccess;
use BadMethodCallException;
use Iterator;
use JsonSerializable;
use RuntimeException;
use Stringable;
use Throwable;

/**
 * Class AutoMappedProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezesFancyHttp\Lib
 * @internal
 */
class AMProxy implements JsonSerializable, ArrayAccess, Iterator, Stringable
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
            if (isset($this->map[$name])) return $this->get($this->map[$name]);
            return $this->getSetSanitized($name);
        } elseif (str_starts_with($name, 'set')) {
            $mappedKey = str_replace('set', 'get', $name);
            if (isset($this->map[$mappedKey])) {
                $this->set($this->map[$mappedKey], $arguments[0]);
            } else {
                $this->getSetSanitized($name, $arguments[0]);
            }
        } else {
            throw new BadMethodCallException("The method {$name} is not legal for AutoMapped interfaces.");
        }
    }

    public function get(string $path): mixed
    {
        $propNames = explode('.', $path);
        $nested = $this->data;
        foreach ($propNames as $propName) {
            if (!isset($nested[$propName])) $this->triggerError($path, $propName);
            $nested = $nested[$propName];
        }
        return $nested;
    }

    private function triggerError(string $path, mixed $propName): void
    {
        throw new RuntimeException("The property path {$path} is invalid. The nested property {$propName} doesn't exists in the data set.");
    }

    protected function getSetSanitized(string $key, mixed $value = null): mixed
    {
        $sanitizedName = substr($this->sanitizeKey($key), 3);
        $mappedKey = $this->keyMap[$sanitizedName];
        if (func_num_args() === 1) {
            return $this->data[$mappedKey];
        } else {
            return $this->data[$mappedKey] = $value;
        }
    }

    public function set(string $path, mixed $value): void
    {
        $propNames = explode('.', $path);
        $paths = array_slice($propNames, 0, -1);
        $nested = &$this->data;
        foreach ($paths as $propName) {
            if (is_array($nested[$propName])) $nested = &$nested[$propName];
            else $this->triggerError($path, $propName);
        }
        $target = array_slice($propNames, -1)[0];
        $nested[$target] = $value;
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
