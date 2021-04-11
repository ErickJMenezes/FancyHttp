<?php


namespace Tests\Clients;

use ArrayAccess;
use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
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
#[AutoMapped([
    'getFooValue' => 'foo'
])]
interface FooInterface extends Stringable, ArrayAccess, JsonSerializable, Iterator
{
    public function getFoo(): string;

    public function getFooValue(): string;
}