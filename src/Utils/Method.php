<?php


namespace ErickJMenezes\FancyHttp\Utils;

use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
use ErickJMenezes\FancyHttp\Attributes\Body;
use ErickJMenezes\FancyHttp\Attributes\Delete;
use ErickJMenezes\FancyHttp\Attributes\FormParams;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\Head;
use ErickJMenezes\FancyHttp\Attributes\HeaderParam;
use ErickJMenezes\FancyHttp\Attributes\HttpVersion;
use ErickJMenezes\FancyHttp\Attributes\Multipart;
use ErickJMenezes\FancyHttp\Attributes\Patch;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Attributes\Post;
use ErickJMenezes\FancyHttp\Attributes\Put;
use ErickJMenezes\FancyHttp\Attributes\QueryParams;
use ErickJMenezes\FancyHttp\Attributes\ReturnsMappedList;
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use ErickJMenezes\FancyHttp\Castable;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Method
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Utils
 */
class Method
{
    protected static array $verbs = [Get::class, Post::class, Put::class, Patch::class, Head::class, Delete::class];

    protected \ReflectionMethod $method;
    protected Parameters $arguments;
    protected \ReflectionAttribute $verb;
    protected string $returnType;
    // Request Arguments
    protected string $path;
    protected array $headers;
    protected array $options;

    /**
     * Method constructor.
     *
     * @param \ReflectionClass $interface
     * @param string           $name
     * @param array            $arguments
     * @throws \ReflectionException
     */
    public function __construct(
        protected \ReflectionClass $interface,
        protected string $name,
        array $arguments
    )
    {
        $this->method = $this->interface->getMethod($name);
        $this->arguments = new Parameters($this->method->getParameters(), $arguments);
        $this->returnType = $this->method->hasReturnType() ? $this->method->getReturnType()->getName() : 'mixed';
        $this->loadVerb();
        $this->loadVerbArguments();
        $this->loadOptions();
    }

    private function loadVerb(): void
    {
        foreach (self::$verbs as $attributeClass) {
            $attributes = $this->method->getAttributes($attributeClass);
            if (!empty($attributes)) {
                $this->verb = $attributes[0];
                return;
            };
        }
        throw new \BadMethodCallException("Verb attribute is missing.");
    }

    private function loadVerbArguments(): void
    {
        [$path, $headers] = $this->verb->getArguments() + ['', []];
        $this->headers = $headers;

        $pathMatches = [];
        preg_match_all('/{(\w+)}/', $path, $pathMatches);
        $pathParameters = $this->arguments->getByAttribute(PathParam::class);
        foreach ($pathMatches[1] ?? [] as $pathArgName) {
            $path = str_replace('{' . $pathArgName . '}', $pathParameters[$pathArgName]->value, $path);
        }
        $this->path = $path;
    }

    protected function loadOptions(): void
    {
        [$bodyType, $bodyContents] = $this->getRequestBody();

        $headers = $this->arguments->getFirstValueByAttribute(HeaderParam::class, []) + $this->getRequestHeaders();
        $queryParams = $this->arguments->getFirstValueByAttribute(QueryParams::class);
        $formParams = $this->arguments->getFirstValueByAttribute(FormParams::class);
        $multipart = $this->arguments->getFirstValueByAttribute(Multipart::class);
        // $httpVersion = $this->arguments->getFirstValueByAttribute(HttpVersion::class);
        $this->options = [
            RequestOptions::HEADERS => $headers,
            $bodyType => $bodyContents,
            RequestOptions::QUERY => $queryParams,
            RequestOptions::FORM_PARAMS => $formParams,
            RequestOptions::MULTIPART => $multipart,
            // RequestOptions::VERSION => $httpVersion,
            RequestOptions::HTTP_ERRORS => !$this->isSuppressed()
        ];
    }

    protected function getRequestBody(): array
    {
        $body = $this->arguments->getFirstByAttribute(Body::class);
        return [$body->attrArgs[0] ?? Body::BODY, $body->value ?? null];
    }

    private function getRequestHeaders(): array
    {
        return $this->verb->getArguments()[1] ?? [];
    }

    private function isSuppressed(): bool
    {
        return !empty($this->method->getAttributes(Suppress::class));
    }

    public function call(ClientInterface $client): mixed
    {
        return $this->castResponse($client->request(
            $this->verb->getName()::METHOD,
            $this->path,
            $this->options
        ));
    }

    protected function castResponse(ResponseInterface $response): mixed
    {
        $decodedResponse = fn() => json_decode($response->getBody()->getContents(), true);
        if ($this->isReturnTypeCastable())
            return $this->returnType::castResponse($response);
        elseif ($modelInterface = $this->isReturnTypeAutoMapped($this->returnType)) {
            return SimpleInterfaceProxy::make($modelInterface, $decodedResponse());
        } elseif ($modelInterface = $this->methodReturnsAutoMappedList()) {
            $data = SimpleInterfaceProxy::makeMany($modelInterface, $decodedResponse());
            return $this->returnType === \ArrayObject::class ? new \ArrayObject($data) : $data;
        }

        return match ($this->returnType) {
            'array' => $decodedResponse(),
            'void', 'null' => null,
            'bool' => true,
            'string' => $response->getBody()->getContents(),
            'int', 'float', 'double' => $response->getStatusCode(),
            'object', \ArrayObject::class => new \ArrayObject($decodedResponse(), \ArrayObject::ARRAY_AS_PROPS),
            ResponseInterface::class, Response::class, 'mixed' => $response,
            default => throw new \RuntimeException("{$this->returnType} is not a valid return type.")
        };
    }

    public function isReturnTypeCastable(): bool
    {
        return class_exists($this->returnType) &&
            !empty(array_filter(
                class_implements($this->returnType),
                fn($interface) => $interface === Castable::class
            ));
    }

    protected function isReturnTypeAutoMapped($returnType): false|\ReflectionClass
    {
        try {
            if (interface_exists($returnType)) {
                $reflection = new \ReflectionClass($this->returnType);
                if (isset($reflection->getAttributes(AutoMapped::class)[0]))
                    return $reflection;
            }
        } catch (\Throwable) {
        }
        return false;
    }

    protected function methodReturnsAutoMappedList(): \ReflectionClass|false
    {
        if ($attribute = $this->method->getAttributes(ReturnsMappedList::class)[0] ?? false) {
            $arg = $attribute->getArguments()[0];
            try {
                if (interface_exists($arg)) {
                    $reflection = new \ReflectionClass($arg);
                    if (isset($reflection->getAttributes(AutoMapped::class)[0]))
                        return $reflection;
                }
            } catch (\Throwable) {
            }
        }

        return false;
    }
}