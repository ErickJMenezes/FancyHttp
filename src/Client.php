<?php


namespace ErickJMenezes\FancyHttp;


use BadMethodCallException;
use ErickJMenezes\FancyHttp\Utils\ClassGenerator;
use ErickJMenezes\FancyHttp\Utils\Method;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;


/**
 * Class ClientProxy
 *
 * @template T
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp
 */
class Client
{
    protected ClientInterface $client;
    protected ReflectionClass $interface;

    /**
     * Client constructor.
     *
     * @param class-string<T> $interfaceClass
     * @param string          $baseUri
     */
    private function __construct(
        protected string $interfaceClass,
        protected string $baseUri
    )
    {
        try {
            $this->interface = new ReflectionClass($interfaceClass);
            if (!$this->interface->isInterface()) $this->throwInvalidArgumentException();
        } catch (ReflectionException $e) {
            $this->throwInvalidArgumentException();
        }
        $this->client = new GuzzleClient(['base_uri' => $this->baseUri]);
    }

    protected function throwInvalidArgumentException(): void
    {
        throw new InvalidArgumentException("The first argument must be a fully qualified interface name.");
    }

    /**
     * @template T
     * @param class-string<T> $interface
     * @param string          $baseUri
     * @return T
     */
    public static function createFromInterface(string $interface, string $baseUri)
    {
        return (new static($interface, $baseUri))->generate();
    }

    /**
     * @return T
     */
    private function generate()
    {
        $codeGenerator = new ClassGenerator($this->interface);
        return $codeGenerator->make($this);
    }

    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->interfaceClass, $name)) {
            throw new BadMethodCallException("The method {$name} is not declared in {$this->interfaceClass}.");
        }

        return (new Method($this->interface, $name, $arguments))->call($this->client);
    }
}