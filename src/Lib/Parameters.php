<?php


namespace ErickJMenezes\FancyHttp\Lib;


use ErickJMenezes\FancyHttp\Attributes\Auth\Basic;
use ErickJMenezes\FancyHttp\Attributes\Auth\Bearer;
use ErickJMenezes\FancyHttp\Attributes\Auth\Digest;
use ErickJMenezes\FancyHttp\Attributes\Auth\Ntml;
use ErickJMenezes\FancyHttp\Attributes\Body;
use ErickJMenezes\FancyHttp\Attributes\FormParams;
use ErickJMenezes\FancyHttp\Attributes\HeaderParam;
use ErickJMenezes\FancyHttp\Attributes\Headers;
use ErickJMenezes\FancyHttp\Attributes\Json;
use ErickJMenezes\FancyHttp\Attributes\Multipart;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Attributes\Query;
use ErickJMenezes\FancyHttp\Attributes\QueryParam;
use ErickJMenezes\FancyHttp\Traits\InteractsWithParameters;
use Exception;


/**
 * Class Parameters
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Lib
 * @internal
 */
class Parameters
{
    use InteractsWithParameters;

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
     */
    public function getFormParams(): array
    {
        return $this->valuesFor(FormParams::class);
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