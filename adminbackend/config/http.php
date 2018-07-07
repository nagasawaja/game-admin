<?php

return [
    'allow-origin' => env('HTTP_ALLOW_ORIGIN', 'http://localhost:8081'),
    'allow-headers' => env('HTTP_ALLOW_HEADERS', 'Origin, Content-Type, Cookie, Accept, token, Set-Cookie'),
    'allow-methods'=>env('HTTP_ALLOW_METHODS','GET, POST, PATCH, PUT, OPTIONS'),
];
