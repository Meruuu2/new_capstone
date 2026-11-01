<?php

$publicDirectory = realpath(__DIR__.'/public');
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$requestedFile = realpath(__DIR__.'/public'.$requestPath);

if (
    $requestedFile !== false
    && $publicDirectory !== false
    && is_file($requestedFile)
    && str_starts_with($requestedFile, $publicDirectory.DIRECTORY_SEPARATOR)
) {
    return false;
}

require __DIR__.'/public/index.php';
