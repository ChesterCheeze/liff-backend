<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-21T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="LineOAUser",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="line_id", type="string", example="U1234567890abcdef"),
 *     @OA\Property(property="name", type="string", example="John LINE User"),
 *     @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-21T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Survey",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Customer Satisfaction Survey"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Please rate your experience with our service"),
 *     @OA\Property(property="status", type="string", enum={"draft", "active", "closed"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="questions", type="array", @OA\Items(ref="#/components/schemas/SurveyQuestion"))
 * )
 * 
 * @OA\Schema(
 *     schema="SurveyQuestion",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="survey_id", type="integer", example=1),
 *     @OA\Property(property="question", type="string", example="How satisfied are you with our service?"),
 *     @OA\Property(property="type", type="string", enum={"text", "radio", "checkbox", "select", "textarea", "number"}, example="radio"),
 *     @OA\Property(property="options", type="string", nullable=true, example="Very Satisfied,Satisfied,Neutral,Unsatisfied,Very Unsatisfied"),
 *     @OA\Property(property="required", type="boolean", example=true),
 *     @OA\Property(property="order", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-21T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="SurveyResponse",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="survey_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="line_user_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="response_data", type="object", example={"1": "Very Satisfied", "2": "Great service!"}),
 *     @OA\Property(property="ip_address", type="string", nullable=true, example="192.168.1.1"),
 *     @OA\Property(property="user_agent", type="string", nullable=true, example="Mozilla/5.0..."),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-21T10:30:00Z"),
 *     @OA\Property(property="survey", ref="#/components/schemas/Survey"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="line_user", ref="#/components/schemas/LineOAUser")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password")
 * )
 * 
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
 * )
 * 
 * @OA\Schema(
 *     schema="LineAuthRequest",
 *     type="object",
 *     required={"line_id", "name"},
 *     @OA\Property(property="line_id", type="string", example="U1234567890abcdef"),
 *     @OA\Property(property="name", type="string", example="John LINE User"),
 *     @OA\Property(property="picture_url", type="string", format="url", example="https://example.com/profile.jpg")
 * )
 * 
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Login successful"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="user", ref="#/components/schemas/User"),
 *         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *         @OA\Property(property="expires_in", type="integer", example=3600)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SurveyRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Customer Satisfaction Survey"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Please rate your experience"),
 *     @OA\Property(property="status", type="string", enum={"draft", "active", "closed"}, example="draft")
 * )
 * 
 * @OA\Schema(
 *     schema="SurveyQuestionRequest",
 *     type="object",
 *     required={"question", "type"},
 *     @OA\Property(property="question", type="string", example="How satisfied are you?"),
 *     @OA\Property(property="type", type="string", enum={"text", "radio", "checkbox", "select", "textarea", "number"}, example="radio"),
 *     @OA\Property(property="options", type="string", nullable=true, example="Very Satisfied,Satisfied,Neutral"),
 *     @OA\Property(property="required", type="boolean", example=true),
 *     @OA\Property(property="order", type="integer", example=1)
 * )
 * 
 * @OA\Schema(
 *     schema="SurveyResponseRequest",
 *     type="object",
 *     required={"survey_id", "response_data"},
 *     @OA\Property(property="survey_id", type="integer", example=1),
 *     @OA\Property(property="response_data", type="object", example={"1": "Very Satisfied", "2": "Great service!"})
 * )
 * 
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation error"),
 *     @OA\Property(property="errors", type="object", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={"email": {"The email field is required."}, "password": {"The password field is required."}}
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://localhost:8000/api/v1/admin/surveys?page=1"),
 *         @OA\Property(property="last", type="string", example="http://localhost:8000/api/v1/admin/surveys?page=5"),
 *         @OA\Property(property="prev", type="string", nullable=true, example=null),
 *         @OA\Property(property="next", type="string", nullable=true, example="http://localhost:8000/api/v1/admin/surveys?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=75)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user")
 * )
 * 
 * @OA\Schema(
 *     schema="ExportRequest",
 *     type="object",
 *     @OA\Property(property="format", type="string", enum={"csv", "xlsx", "json"}, example="xlsx"),
 *     @OA\Property(
 *         property="filters",
 *         type="object",
 *         @OA\Property(property="date_from", type="string", format="date", example="2024-01-01"),
 *         @OA\Property(property="date_to", type="string", format="date", example="2024-12-31"),
 *         @OA\Property(property="status", type="string", enum={"draft", "active", "closed"}, example="active")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ImportValidationRequest",
 *     type="object",
 *     required={"file"},
 *     @OA\Property(property="file", type="string", format="binary", description="File to validate for import")
 * )
 * 
 * @OA\Schema(
 *     schema="DashboardAnalytics",
 *     type="object",
 *     @OA\Property(property="total_surveys", type="integer", example=25),
 *     @OA\Property(property="active_surveys", type="integer", example=8),
 *     @OA\Property(property="total_responses", type="integer", example=1250),
 *     @OA\Property(property="total_users", type="integer", example=150),
 *     @OA\Property(property="response_rate", type="number", format="float", example=75.5),
 *     @OA\Property(
 *         property="recent_activity",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="type", type="string", example="survey_created"),
 *             @OA\Property(property="message", type="string", example="New survey 'Customer Feedback' created"),
 *             @OA\Property(property="timestamp", type="string", format="date-time", example="2024-07-21T10:30:00Z")
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SurveyStats",
 *     type="object",
 *     @OA\Property(property="survey_id", type="integer", example=1),
 *     @OA\Property(property="total_responses", type="integer", example=150),
 *     @OA\Property(property="completion_rate", type="number", format="float", example=85.5),
 *     @OA\Property(property="average_completion_time", type="integer", description="Average time in seconds", example=180),
 *     @OA\Property(
 *         property="response_breakdown",
 *         type="object",
 *         @OA\Property(property="today", type="integer", example=12),
 *         @OA\Property(property="this_week", type="integer", example=45),
 *         @OA\Property(property="this_month", type="integer", example=150)
 *     ),
 *     @OA\Property(
 *         property="question_stats",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="question_id", type="integer", example=1),
 *             @OA\Property(property="response_count", type="integer", example=140),
 *             @OA\Property(property="skip_rate", type="number", format="float", example=6.7)
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ExportResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Export completed successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="download_url", type="string", example="https://example.com/exports/survey-data-20240721.xlsx"),
 *         @OA\Property(property="file_name", type="string", example="survey-data-20240721.xlsx"),
 *         @OA\Property(property="file_size", type="integer", example=1024576),
 *         @OA\Property(property="expires_at", type="string", format="date-time", example="2024-07-22T10:30:00Z")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ImportResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Import completed successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="processed_records", type="integer", example=150),
 *         @OA\Property(property="successful_imports", type="integer", example=145),
 *         @OA\Property(property="failed_imports", type="integer", example=5),
 *         @OA\Property(
 *             property="errors",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="row", type="integer", example=23),
 *                 @OA\Property(property="error", type="string", example="Invalid email format")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="is_valid", type="boolean", example=true),
 *         @OA\Property(property="total_rows", type="integer", example=150),
 *         @OA\Property(property="valid_rows", type="integer", example=145),
 *         @OA\Property(property="invalid_rows", type="integer", example=5),
 *         @OA\Property(
 *             property="preview",
 *             type="array",
 *             @OA\Items(type="object", example={"name": "Sample Survey", "description": "Test description"})
 *         ),
 *         @OA\Property(
 *             property="errors",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="row", type="integer", example=23),
 *                 @OA\Property(property="field", type="string", example="email"),
 *                 @OA\Property(property="error", type="string", example="Invalid format")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="TemplateResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="download_url", type="string", example="https://example.com/templates/survey-import-template.xlsx"),
 *         @OA\Property(property="file_name", type="string", example="survey-import-template.xlsx")
 *     )
 * )
 */
class Schemas
{
    // This class exists solely for OpenAPI schema definitions
}