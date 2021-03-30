<?php


namespace ErickJMenezes\FancyHttp\Utils;


/**
 * Class TemplateGenerator
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp\Utils
 * @template T
 */
class InterfaceProxy implements \JsonSerializable
{
    protected array $keyMap = [];

    private function __construct(protected array $data)
    {
        foreach ($data as $key => $value) $this->keyMap[strtolower($key)] = $key;
    }

    public function __call(string $name, array $arguments)
    {
        $sanitized = strtolower(substr($name, 3));
        if (str_starts_with($name, 'get')) {
            return $this->data[$this->keyMap[$sanitized]];
        } elseif (str_starts_with($name, 'set')) {
            $this->data[$this->keyMap[$sanitized]] = $arguments[0];
            return;
        }
        throw new \BadMethodCallException("The method {$name} is invalid. Only use getters and setters.");
    }

    /**
     * @param \ReflectionClass $interface
     * @param array            $data
     * @return T
     * @throws \ReflectionException
     */
    public static function make(\ReflectionClass $interface, array $data)
    {
        return (new ClassGenerator($interface))->make(new static($data));
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
