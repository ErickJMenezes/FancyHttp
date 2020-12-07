# Fancy HTTP
#### A http client to implements your client interfaces for you! 

Look the example down below:
~~~php
use ErickJMenezes\FancyHttp\Attributes\Api;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Client;

#[Api]
interface MyClient {
    #[Get('todos/{id}')]
    public function getTodoById(
        #[PathParam('id')] string $id
    ): array; // the response will be auto casted to array.
}

$myClient = Client::createFromInterface(
    MyClient::class, // uses the interface fully qualified class name.
    'http://localhost:9000/api/' // The webservices base uri.
);

// Call the method declared in interface.
$todo = $myClient->getTodoById(1);

echo $todo['description']; // echo the todo's description
~~~
It's simple as that!

## How to install?
(Not published yet)
~~~shell
$ composer install --no-dev erickjmenezes/fancyhttp
~~~

## Documentation
Soon...