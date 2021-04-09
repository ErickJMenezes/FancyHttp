<?php


namespace ErickJMenezes\FancyHttp\Traits\Concerns;

use BadMethodCallException;
use ErickJMenezes\FancyHttp\Attributes\AbstractHttpMethod;
use ErickJMenezes\FancyHttp\Attributes\Delete;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\Head;
use ErickJMenezes\FancyHttp\Attributes\Patch;
use ErickJMenezes\FancyHttp\Attributes\Post;
use ErickJMenezes\FancyHttp\Attributes\Put;
use ReflectionMethod;

/**
 * Trait InteractsWithMethodAttributes
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Traits\Concerns
 */
trait InteractsWithMethodAttributes
{
    use InteractsWithAttributes {
        getAttributeInstance as getReflectionAttributeInstance;
        hasAttribute as reflectionHasAttribute;
    }

    /**
     * @var array<class-string<AbstractHttpMethod>>
     */
    protected static array $verbs = [Get::class, Post::class, Put::class, Patch::class, Head::class, Delete::class];

    protected AbstractHttpMethod $verb;

    protected function loadVerb(): void
    {
        foreach (self::$verbs as $verb) {
            if ($this->hasAttribute($verb)) {
                $this->verb = $this->getAttributeInstance($verb);
                return;
            }
        }
        throw new BadMethodCallException("The method {$this->method()->getName()} has no verb attribute.");
    }

    /**
     * @param class-string $name
     * @return bool
     */
    protected function hasAttribute(string $name): bool
    {
        return $this->reflectionHasAttribute($this->method(), $name);
    }

    abstract protected function method(): ReflectionMethod;

    /**
     * @param class-string<T> $name
     * @return T
     * @template T of object
     */
    protected function getAttributeInstance(string $name): object
    {
        return $this->getReflectionAttributeInstance($this->method(), $name);
    }
}