<?php


namespace Tests\Clients;


use FancyHttp\Attributes\AutoMapped;
use FancyHttp\Attributes\MapTo;

#[AutoMapped]
interface BarInterface
{
    #[MapTo('bar')]
    public function getBar(): string;
}