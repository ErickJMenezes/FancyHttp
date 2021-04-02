<?php


namespace ErickJMenezes\FancyHttp;


use BadMethodCallException;
use ErickJMenezes\FancyHttp\Utils\Implementer;
use ErickJMenezes\FancyHttp\Utils\Method;
use ErickJMenezes\FancyHttp\Utils\Parameters;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;


/**
 * Class ClientProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp
 * @template T
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
        string $interfaceClass,
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
    private function generate(): mixed
    {
        $implementer = new Implementer($this->interface);
        return $implementer->make($this);
    }

    public function __call(string $name, array $arguments)
    {
        if (!$this->interface->hasMethod($name)) {
            throw new BadMethodCallException("The method {$name} is not declared in {$this->interface->getName()}.");
        }

        return (new Method(
            $reflectedMethod = $this->interface->getMethod($name),
            new Parameters($reflectedMethod->getParameters(), $arguments)
        ))->call($this->client);
    }
}