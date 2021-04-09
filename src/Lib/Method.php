<?php


namespace ErickJMenezes\FancyHttp\Lib;

use ArrayObject;
use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
use ErickJMenezes\FancyHttp\Attributes\ReturnsMappedList;
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use ErickJMenezes\FancyHttp\Castable;
use ErickJMenezes\FancyHttp\Traits\Concerns\CreatesObjectProxies;
use ErickJMenezes\FancyHttp\Traits\Concerns\InteractsWithMethodAttributes;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * Class Method
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezesFancyHttp\Lib
 * @template T
 */
class Method
{
    use InteractsWithMethodAttributes;
    use CreatesObjectProxies;

    protected ?ResponseInterface $lastResponse = null;
    protected string $returnType;

    /**
     * Method constructor.
     *
     * @param \ReflectionMethod                       $method
     * @param \ErickJMenezes\FancyHttp\Lib\Parameters $parameters
     */
    public function __construct(
        protected ReflectionMethod $method,
        protected Parameters $parameters
    )
    {
        $this->loadVerb();
        $this->returnType = $this->method->getReturnType()?->getName() ?? 'mixed';
    }

    /**
     * @param \GuzzleHttp\ClientInterface $client
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function call(ClientInterface $client): mixed
    {
        return $this->castResponse($this->lastResponse = $client->request(
            $this->verb::method(),
            $this->parameters->parsePath($this->verb->path),
            $this->getOptions()
        ));
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return mixed
     * @throws \Exception
     */
    protected function castResponse(ResponseInterface $response): mixed
    {
        if (is_a($this->returnType, Castable::class, true)) {
            return $this->returnType::castResponse($response);
        } elseif ($modelInterface = $this->isReturnTypeAutoMapped()) {
            return self::createProxy($modelInterface, $this->decodeResponse($response));
        } elseif ($modelInterface = $this->methodReturnsAutoMappedList()) {
            $data = self::createProxies($modelInterface, $this->decodeResponse($response));
            return $this->returnType === ArrayObject::class ? new ArrayObject($data) : $data;
        }

        return match ($this->returnType) {
            'array' => $this->decodeResponse($response),
            'void', 'null' => null,
            'bool' => true,
            'string' => $response->getBody()->getContents(),
            'int', 'float', 'double' => $response->getStatusCode(),
            'object', ArrayObject::class => new ArrayObject($this->decodeResponse($response), ArrayObject::ARRAY_AS_PROPS),
            ResponseInterface::class, Response::class, 'mixed' => $response,
            default => throw new RuntimeException("{$this->returnType} is not a valid return type.")
        };
    }

    protected function isReturnTypeAutoMapped(): false|ReflectionClass
    {
        return $this->isAnAutoMappedInterface($this->returnType);
    }

    /**
     * @param class-string|mixed $value
     * @return false|\ReflectionClass
     */
    protected function isAnAutoMappedInterface(mixed $value): false|ReflectionClass
    {
        try {
            $reflection = new ReflectionClass($value);
            if (
                $reflection->isInterface() &&
                !empty($reflection->getAttributes(AutoMapped::class))
            )
                return $reflection;
        } catch (ReflectionException) {
        }
        return false;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        return (array)json_decode($response->getBody()->getContents(), true);
    }

    protected function methodReturnsAutoMappedList(): ReflectionClass|false
    {
        if ($this->hasAttribute(ReturnsMappedList::class)) {
            $instance = $this->getAttributeInstance(ReturnsMappedList::class);
            return $this->isAnAutoMappedInterface($instance->interface);
        }
        return false;
    }

    protected function getOptions(): array
    {
        return array_filter([
                RequestOptions::HEADERS => $this->parameters->getHeaderParams(),
                RequestOptions::BODY => $this->parameters->getBodyParam(),
                RequestOptions::JSON => $this->parameters->getJsonParam(),
                RequestOptions::QUERY => $this->parameters->getQueryParameters(),
                RequestOptions::FORM_PARAMS => $this->parameters->getFormParams(),
                RequestOptions::MULTIPART => $this->parameters->getMultipartParams(),
                RequestOptions::AUTH => $this->parameters->getAuthParams()
            ]) + [RequestOptions::HTTP_ERRORS => !$this->hasAttribute(Suppress::class)];
    }

    public function getLastGuzzleResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    protected function method(): ReflectionMethod
    {
        return $this->method;
    }
}