<?php

use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

$app = require __DIR__ . '/../bootstrap/app.php';
$loop = React\EventLoop\Factory::create();
$handler = function (ServerRequestInterface $request) use ($app) {
    $parsedBody = $request->getParsedBody();
    if (empty($parsedBody) || !is_array($parsedBody)) {
        $parsedBody = [];
    }
    $lumenRequest = Request::create(
        $request->getUri(),
        $request->getMethod(),
        array_merge($request->getQueryParams(), $parsedBody),
        $request->getCookieParams(),
        $request->getUploadedFiles(),
        $request->getServerParams(),
        $request->getBody()->getContents()
    );
    $lumenRequest->headers = new HeaderBag($request->getHeaders());
    $response = $app->dispatch($lumenRequest);
    return new React\Http\Response(
        $response->getStatusCode(),
        $response->headers->all(),
        $response->getContent()
    );
};
$server = new React\Http\Server($handler);
$port = 8000;
$socket = new React\Socket\Server($port, $loop);
$server->listen($socket);
$loop->run();
