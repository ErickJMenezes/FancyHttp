<?php


namespace ErickJMenezes\FancyHttp;


use BadMethodCallException;
use ErickJMenezes\FancyHttp\Lib\Implementer;
use ErickJMenezes\FancyHttp\Lib\Method;
use ErickJMenezes\FancyHttp\Lib\Parameters;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;


/**
 * Class ClientProxy
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezes\FancyHttp
 * @template T of object
 */
class Client
{
    public ?ResponseInterface $lastResponse = null;
    protected ClientInterface $client;
    /** @var \ReflectionClass<T> $interface */
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
        ?string $baseUri = null,
        array $guzzleOptions = []
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
     * @param class-string<I>     $interface
     * @param string|null         $baseUri
     * @param array<string,mixed> $guzzleOptions
     * @return I
     * @template I of object
     * @throws \Exception
     */
    public static function createFromInterface(string $interface, string $baseUri = null, array $guzzleOptions = []): object
    {
        return (new self($interface, $baseUri, $guzzleOptions))->generate();
    }

    /**
     * @return T
     * @throws \Exception
     */
    protected function generate(): mixed
    {
        return (new Implementer($this->interface))->make($this);
    }

    /**
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
        $reflectedMethod = $this->interface->getMethod($name);
        $method = new Method(
            $reflectedMethod,
            new Parameters($reflectedMethod->getParameters(), $arguments),
            $this
        );
        return $method->call($this->client);
    }
}