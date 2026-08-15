<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Pickleball Hub API',
    version: '1.0.0',
    description: 'API documentation for Pickleball Hub'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token'
)]
class Swagger {}
