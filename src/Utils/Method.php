<?php


namespace ErickJMenezes\FancyHttp\Utils;

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
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use ErickJMenezes\FancyHttp\Castable;
use GuzzleHttp\ClientInterface;
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
    protected MethodArguments $arguments;
    protected \ReflectionAttribute $verb;
    // Request Arguments
    protected string $path;
    protected array $headers;
    protected array $options;

    /**
     * Method constructor.
     *
     * @param \ReflectionClass                               $interface
     * @param string                                         $name
     * @param \ErickJMenezes\FancyHttp\Utils\MethodArguments $arguments
     * @throws \ReflectionException
     */
    public function __construct(
        protected \ReflectionClass $interface,
        protected string $name,
        array $arguments
    )
    {
        $this->method = $this->interface->getMethod($name);
        $this->arguments = new MethodArguments($this->method->getParameters(), $arguments);
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
            $path = str_replace('{'.$pathArgName.'}', $pathParameters[$pathArgName]->value, $path);
        }
        $this->path = $path;
    }

    public function call(ClientInterface $client): mixed
    {
        return $this->castResponse($client->request(
            $this->verb->getName()::METHOD,
            $this->path,
            $this->options
        ));
    }

    protected function getRequestBody(): array
    {
        $body = $this->arguments->getFirstByAttribute(Body::class, null, true);
        return [$body->attrArgs[0] ?? Body::BODY, $body->value ?? null];
    }

    protected function loadOptions(): void
    {
        [$bodyType, $bodyContents] = $this->getRequestBody();

        $headers = $this->arguments->getFirstByAttribute(HeaderParam::class, []) + $this->getRequestHeaders();
        $queryParams = $this->arguments->getFirstByAttribute(QueryParams::class);
        $formParams = $this->arguments->getFirstByAttribute(FormParams::class);
        $multipart = $this->arguments->getFirstByAttribute(Multipart::class);
        $httpVersion = $this->arguments->getFirstByAttribute(HttpVersion::class);
        $this->options = array_filter([
                RequestOptions::HEADERS => $headers,
                $bodyType => $bodyContents,
                RequestOptions::QUERY => $queryParams,
                RequestOptions::FORM_PARAMS => $formParams,
                RequestOptions::MULTIPART => $multipart,
                RequestOptions::VERSION => $httpVersion
            ]) + [
                RequestOptions::HTTP_ERRORS => !$this->isSuppressed()
            ];
    }

    private function getRequestHeaders(): array
    {
        return $this->verb->getArguments()[1] ?? [];
    }

    private function isSuppressed(): bool
    {
        return !empty($this->method->getAttributes(Suppress::class));
    }

    public function getReturnType(): string
    {
        return $this->method->hasReturnType() ? $this->method->getReturnType() : 'mixed';
    }

    protected function castResponse(ResponseInterface $response): mixed
    {
        $returnType = $this->getReturnType();
        if (class_exists($returnType) && class_implements($returnType))
            return $returnType::castResponse($response);

        $stringResponse = $response->getBody()->getContents();
        return match ($returnType) {
            'array' => json_decode($stringResponse ?: '{}', true),
            'void', 'null' => null,
            'bool' => true,
            'string' => $stringResponse,
            'object', \ArrayObject::class => new \ArrayObject(
                json_decode($stringResponse ?: '{}', true)
                , \ArrayObject::ARRAY_AS_PROPS
            ),
            default => $response
        };
    }
}