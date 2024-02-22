<?php

require __DIR__ . '/vendor/autoload.php';

$http = new React\Http\HttpServer(function (Psr\Http\Message\ServerRequestInterface $request) {
    // Define the root directory for your files
    $rootDir = __DIR__ . '/www';

    // Get the requested URI path
    $uriPath = $request->getUri()->getPath();

    // Get the requested domain from the Host header
    $host = $request->getHeader('Host')[0];

    // Construct the file path based on the requested domain and URI
    $filePath = $rootDir . ($uriPath === '/' ? '/index.php' : $uriPath);

    if (!file_exists($filePath)) {
        return new React\Http\Message\Response(
            404,
            ['Content-Type' => 'text/plain'],
            '404 Not Found'
        );
    }

    // Use output buffering to capture the output of the included PHP file
    ob_start();
    include $filePath;
    $fileContent = ob_get_clean();

    if ($fileContent === false) {
        return new React\Http\Message\Response(
            500,
            ['Content-Type' => 'text/plain'],
            '500 Internal Server Error'
        );
    }

    // Send the captured output as the HTTP response
    return new React\Http\Message\Response(
        200,
        ['Content-Type' => 'text/html'],
        $fileContent
    );
});

$socket = new React\Socket\SocketServer('127.0.0.1:8080');
$http->listen($socket);

echo "Server running at http://127.0.0.1:8080" . PHP_EOL;
