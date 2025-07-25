<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="My API Documentation",
 *     description="Official API docs for my Laravel app.",
 *     @OA\Contact(
 *         email="you@example.com"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     name="Authorization",
 *     in="header",
 *     description="Enter your Bearer token to access this endpoint"
 * )
 */
class SwaggerController {}
