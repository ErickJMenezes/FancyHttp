<?php


namespace ErickJMenezes\FancyHttp;


use BadMethodCallException;
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
    public function __construct(
        protected string $interfaceClass,
        protected string $baseUri
    )
    {
        try {
            $this->interface = new ReflectionClass($interfaceClass);
            if (!$this->interface->isInterface()) {
                $this->throwInvalidArgumentException("The first argument must be a fully qualified interface name.");
            }
        } catch (ReflectionException $e) {
            $this->throwInvalidArgumentException($e->getMessage());
        }
        $this->client = new GuzzleClient(['base_uri' => $this->baseUri]);
    }

    protected function throwInvalidArgumentException(string $message = ''): void
    {
        throw new InvalidArgumentException($message);
    }

    /**
     * @template T
     * @param class-string<T> $interface
     * @param string          $baseUri
     * @return static<T>
     */
    public static function createFromInterface(string $interface, string $baseUri): static
    {
        return new static($interface, $baseUri);
    }

    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->interfaceClass, $name)) {
            $this->throwBadMethodCallException("The method {$name} is not declared in {$this->interfaceClass}.");
        }

        return (new Method($this->interface, $name, $arguments))->call($this->client);
    }

    /**
     * @param string $message
     * @throws \BadMethodCallException
     */
    protected function throwBadMethodCallException(string $message = ''): void
    {
        throw new BadMethodCallException($message);
    }
}