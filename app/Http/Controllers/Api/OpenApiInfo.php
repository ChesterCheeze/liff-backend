<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     title="Laravel Survey API",
 *     description="A comprehensive survey management API built with Laravel, featuring:
 * - Multi-role authentication (Admin, User, LINE OA)
 * - Real-time survey updates via WebSocket broadcasting
 * - Advanced analytics and reporting
 * - Export/Import functionality for surveys and responses
 * - Tiered rate limiting for security
 * - Email verification and password reset",
 *     version="1.0.0",
 *     contact={
 *         "name": "API Support",
 *         "email": "support@example.com"
 *     },
 *     license={
 *         "name": "MIT",
 *         "url": "https://opensource.org/licenses/MIT"
 *     }
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT token obtained from authentication endpoints"
 * )
 */
class OpenApiInfo
{
    // This class exists solely for OpenAPI annotations
}