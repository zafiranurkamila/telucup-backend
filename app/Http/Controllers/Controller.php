<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "TelU Cup Backend API Documentation",
    description: "Detailed and professional API documentation for the TelU Cup backend project."
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Primary API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}
