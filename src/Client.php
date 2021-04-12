<?php


namespace ErickJMenezes\FancyHttp;


use BadMethodCallException;
use ErickJMenezes\FancyHttp\Lib\Implementer;
use ErickJMenezes\FancyHttp\Lib\Method;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;


/**
 * Class Client
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp
 * @template T of object
 */
class Client
{
    /**
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    public ?ResponseInterface $lastResponse = null;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var \ReflectionClass<T> $interface
     */
    protected ReflectionClass $interface;

    /**
     * Client constructor.
     *
     * @param class-string<T>     $interfaceClass
     * @param string|null         $baseUri
     * @param array<string,mixed> $guzzleOptions
     * @throws \InvalidArgumentException
     */
    protected function __construct(
        string $interfaceClass,
        ?string $baseUri,
        array $guzzleOptions
    )
    {
        $invalidArgumentException = new InvalidArgumentException("The value \"{$interfaceClass}\" is not a valid fully qualified interface name.");
        try {
            $this->interface = new ReflectionClass($interfaceClass);
            if (!$this->interface->isInterface()) throw $invalidArgumentException;
        } catch (ReflectionException) {
            throw $invalidArgumentException;
        }
        $this->client = new GuzzleClient(['base_uri' => $baseUri] + $guzzleOptions);
    }

    /**
     * Creates a new http client instance using the fully qualified class name of any valid
     * PHP interface.
     *
     * @param class-string<I>     $interface The fully qualified class name of your interface.
     *                                       Example: MyClientInterface::class
     * @param string|null         $baseUri [Optional] The base URI of the API you want to consume.
     *                                     Example: https://app.exampledomain.com/api/
     * @param array<string,mixed> $guzzleOptions [Optional] Here you can define some specific guzzle options.
     * @return I
     * @template I of object
     * @throws \Exception
     */
    public static function createForInterface(string $interface, string $baseUri = null, array $guzzleOptions = []): object
    {
        return (new self($interface, $baseUri, $guzzleOptions))->generate();
    }

    /**
     * Here's where the anonymous class that implements the user's client interface.
     * is generated at runtime.
     *
     * @return T
     * @throws \Exception
     */
    protected function generate(): object
    {
        return (new Implementer($this->interface))->make($this);
    }

    /**
     * Here's where the generated client calls are
     * handled and dispatched to guzzle's client.
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __call(string $name, array $arguments): mixed
    {
        !$this->interface->hasMethod($name) &&
        throw new BadMethodCallException("The method {$name} is not declared in {$this->interface->getName()}.");
        return $this->callClientMethod($name, $arguments);
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function callClientMethod(string $name, array $arguments): mixed
    {
        return (new Method(
            $this->interface->getMethod($name),
            $arguments,
            $this,
        ))->call($this->client);
    }
}