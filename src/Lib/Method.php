<?php


namespace ErickJMenezes\FancyHttp\Lib;

use ArrayObject;
use ErickJMenezes\FancyHttp\Castable;
use ErickJMenezes\FancyHttp\Traits\InteractsWithMethods;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class Method
 *
 * @author   ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package  ErickJMenezesFancyHttp\Lib
 * @template T
 * @internal
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Method
{
    use InteractsWithMethods;

    /**
     * @param \GuzzleHttp\ClientInterface $client
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function call(ClientInterface $client): mixed
    {
        return $this->castResponse($this->parent->lastResponse = $client->request(
            $this->verb::method(),
            $this->parameters->parsePath($this->verb->path),
            $this->getOptions()
        ));
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function castResponse(ResponseInterface $response): mixed
    {
        if (is_a($this->returnType, Castable::class, true)) {
            return $this->returnType::castResponse($response);
        } elseif ($this->returnAutoMapped()) {
            return $this->createProxy($this->getAutoMappedInterface(), $this->decodeResponse($response));
        } elseif ($this->returnMappedList()) {
            $data = $this->createProxies($this->getMappedListInterface(), $this->decodeResponse($response));
            return $this->returnType === ArrayObject::class ? new ArrayObject($data) : $data;
        }

        return match ($this->returnType) {
            'array' => $this->decodeResponse($response),
            'void', 'null', null => null,
            'bool' => true,
            'string' => $response->getBody()->getContents(),
            'int', 'float', 'double' => $response->getStatusCode(),
            'object', ArrayObject::class => new ArrayObject($this->decodeResponse($response), ArrayObject::ARRAY_AS_PROPS),
            ResponseInterface::class, Response::class, 'mixed' => $response,
            default => throw new RuntimeException("{$this->returnType} is not a valid return type.")
        };
    }

    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
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
            ]) + [
                RequestOptions::HTTP_ERRORS => !$this->isSuppressed()
            ];
    }
}