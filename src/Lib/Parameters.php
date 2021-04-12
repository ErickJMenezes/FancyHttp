<?php


namespace FancyHttp\Lib;


use FancyHttp\Attributes\Auth\Basic;
use FancyHttp\Attributes\Auth\Bearer;
use FancyHttp\Attributes\Auth\Digest;
use FancyHttp\Attributes\Auth\Ntml;
use FancyHttp\Attributes\Body;
use FancyHttp\Attributes\FormParams;
use FancyHttp\Attributes\HeaderParam;
use FancyHttp\Attributes\Headers;
use FancyHttp\Attributes\Json;
use FancyHttp\Attributes\Multipart;
use FancyHttp\Attributes\PathParam;
use FancyHttp\Attributes\Query;
use FancyHttp\Attributes\QueryParam;
use FancyHttp\Traits\InteractsWithParameters;
use Exception;


/**
 * Class Parameters
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Lib
 * @internal
 */
class Parameters
{
    use InteractsWithParameters;

    /**
     * @param array<\ReflectionParameter> $reflectionParameters
     * @param array                      $arguments
     */
    public function __construct(array $reflectionParameters, array $arguments)
    {
        $this->reflectionParameters = $reflectionParameters;
        $this->arguments = $arguments;
        $this->loadParameterMap();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getQueryParameters(): array
    {
        return $this->valuesForPair(
            Query::class,
            QueryParam::class
        );
    }

    /**
     * @return array<string,string>
     * @throws \Exception
     */
    public function getHeaderParams(): array
    {
        return $this->valuesForPair(
                Headers::class,
                HeaderParam::class
            ) + $this->getBearerParam();
    }

    /**
     * @return array<string,string>
     * @throws \Exception
     */
    public function getBearerParam(): array
    {
        $token = $this->valueFor(Bearer::class);
        if (is_null($token)) return [];
        return ['Authorization' => "Bearer {$token}"];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFormParams(): ?array
    {
        return $this->valueFor(FormParams::class);
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getMultipartParams(): ?array
    {
        return $this->valueFor(Multipart::class);
    }

    /**
     * @return array<string>|null
     * @throws \Exception
     */
    public function getAuthParams(): ?array
    {
        if ($value = $this->valueFor(Basic::class)) {
            $value[] = 'basic';
        } elseif ($value = $this->valueFor(Digest::class)) {
            $value[] ='digest';
        } elseif ($value = $this->valueFor(Ntml::class)) {
            $value[] = 'ntml';
        }
        return $value;
    }

    /**
     * @throws \Exception
     */
    public function getBodyParam(): ?string
    {
        return $this->valueFor(Body::class);
    }

    /**
     * @return array<string,mixed>|null
     * @throws \Exception
     */
    public function getJsonParam(): ?array
    {
        return $this->valueFor(Json::class);
    }

    /**
     * @param string $path
     * @return string
     * @throws \Exception
     */
    public function parsePath(string $path): string
    {
        $pathParameters = $this->withAttributes(PathParam::class);
        foreach ($pathParameters as $pathParameter) {
            $attributeInstance = $this->getAttributeInstance($pathParameter, PathParam::class);
            $value = $this->get($pathParameter);
            $attributeInstance->check($value);
            $placeholder = $attributeInstance->name;
            $count = 0;
            $path = str_replace('{' . $placeholder . '}', $value, $path, $count);
            if ($count === 0) {
                throw new Exception("The argument \"{$pathParameter->getName()}\" is not used by any path parameter.");
            } elseif ($count > 1) {
                throw new Exception("The path parameter \"{$placeholder}\" is repeated.");
            }
        }
        $missing = [];
        if (preg_match('/{.*?}/', $path, $missing)) {
            throw new Exception("The path parameter \"{$missing[0]}\" has no replacement");
        }
        return $path;
    }
}