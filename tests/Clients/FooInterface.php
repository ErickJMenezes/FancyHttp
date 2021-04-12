<?php


namespace Tests\Clients;

use ArrayAccess;
use FancyHttp\Attributes\AutoMapped;
use FancyHttp\Attributes\MapTo;
use Iterator;
use JsonSerializable;
use Stringable;

/**
 * Interface TodoInterface
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 * @property string $foo
 */
#[AutoMapped]
interface FooInterface extends Stringable, ArrayAccess, JsonSerializable, Iterator
{
    #[MapTo('foo')]
    public function getFoo(): string;

    #[MapTo('foo')]
    public function setFoo(string $value): self;
}