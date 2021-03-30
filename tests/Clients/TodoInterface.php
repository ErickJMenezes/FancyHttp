<?php


namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Attributes\AutoMapped;

/**
 * Interface TodoInterface
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 */
#[AutoMapped]
interface TodoInterface
{
    public function getUserId(): int;

    public function getId(): int;

    public function getTitle(): string;

    public function setTitle(string $value): void;

    public function getCompleted(): bool;
}