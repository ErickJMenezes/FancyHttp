<?php

require __DIR__ . '/vendor/autoload.php';

use ErickJMenezes\FancyHttp\Attributes\Auth\Bearer;
use ErickJMenezes\FancyHttp\Attributes\AutoMapped;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\Json;
use ErickJMenezes\FancyHttp\Attributes\Post;
use ErickJMenezes\FancyHttp\Client;

#[AutoMapped]
interface Authorization extends \Stringable
{
    public function getAccessToken(): string;
}

/**
 * Interface User
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
#[AutoMapped([
    'getName' => 'data.name'
])]
interface User extends \Stringable
{
    public function getName(): string;

    public function setName(string $value): void;
}

interface KividClient
{
    #[Post('auth/login')]
    public function login(#[Json] array $body): Authorization;

    #[Get('auth/user')]
    public function user(#[Bearer] string $token): User;
}

$kivid = Client::createFromInterface(KividClient::class, 'http://localhost:8000/api/');

$token = $kivid->login([
    'email' => 'vitorpizarro@gmail.com',
    'password' => '123456'
]);

$user = $kivid->user($token->getAccessToken());
$user->setName('erick');
printf($user->getName());