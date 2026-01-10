<?php

$config = require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$meta = $config['meta'];

$rawCode = $_SERVER['REDIRECT_STATUS'] ?? null;

$code = in_array((int)$rawCode, [401, 403, 404, 500, 502]) ? (int)$rawCode : 404;

$codes = [
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    500 => 'Internal Server Error',
    502 => 'Page Disabled',
];

$title = $codes[$code] ?? 'Unknown Error';
$message = match ($code) {
    401 => 'You are not authorized to view this page.',
    403 => 'Access to this page is forbidden.',
    404 => 'The page you are looking for could not be found.',
    500 => 'The server encountered an internal error.',
    502 => 'This page has been disabled by an admin. If this is a store page, please ask an admin to re-enable the store.',
    default => 'An unexpected error occurred.',
};

http_response_code((int)$code);
?>