<?php


namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Attributes\AutoMapped;

/**
 * Interface TodoInterface
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 * @property int $userId
 * @property int $id
 * @property string $title
 * @property bool $completed
 */
#[AutoMapped]
interface TodoInterface extends \ArrayAccess, \JsonSerializable
{
    public function getUserId(): int;

    public function setUserId(int $id): void;

    public function getId(): int;

    public function setId(int $id): void;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getCompleted(): bool;

    public function setCompleted(bool $completed): void;
}