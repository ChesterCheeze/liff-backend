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
 *     url="http://localhost:8000",
 *     description="Development server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.example.com",
 *     description="Production server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT token obtained from authentication endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and registration"
 * )
 * 
 * @OA\Tag(
 *     name="Surveys",
 *     description="Public survey access"
 * )
 * 
 * @OA\Tag(
 *     name="Survey Responses",
 *     description="Survey response submission and retrieval"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Surveys",
 *     description="Survey management for administrators"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Questions",
 *     description="Survey question management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Users",
 *     description="User management for administrators"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Analytics",
 *     description="Analytics and reporting"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Export",
 *     description="Data export functionality"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Import",
 *     description="Data import functionality"
 * )
 * 
 * @OA\Tag(
 *     name="Broadcasting",
 *     description="Real-time WebSocket authentication"
 * )
 * 
 * @OA\Tag(
 *     name="Legacy",
 *     description="Legacy endpoints for backward compatibility"
 * )
 */
class OpenApiInfo
{
    // This class exists solely for OpenAPI annotations
}