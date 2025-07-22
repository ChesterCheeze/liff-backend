# API Code Review Checklist & Best Practices

## Purpose
This document serves as a guide for code reviews and quality assurance when developing API features. Use this checklist to ensure consistent, high-quality API implementations.

## Code Review Checklist

### 1. Controller Implementation ✅

#### Structure & Organization
- [ ] Controller extends `BaseApiController`
- [ ] Uses appropriate traits (`ApiResponseTrait`)
- [ ] Methods follow RESTful conventions
- [ ] Proper namespace and imports
- [ ] Methods are focused and single-purpose

#### Error Handling
- [ ] Uses consistent error response format
- [ ] Handles edge cases gracefully
- [ ] Provides meaningful error messages
- [ ] Proper HTTP status codes

#### Example - Good Controller Method:
```php
public function store(StoreSurveyRequest $request)
{
    try {
        $survey = Survey::create($request->validated());
        
        return $this->successResponse(
            new SurveyResource($survey),
            'Survey created successfully',
            201
        );
    } catch (\Exception $e) {
        Log::error('Survey creation failed', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
        ]);
        
        return $this->serverErrorResponse('Failed to create survey');
    }
}
```

### 2. Request Validation ✅

#### Form Requests
- [ ] Uses dedicated Form Request classes
- [ ] Validation rules are appropriate and complete
- [ ] Custom error messages where needed
- [ ] Authorization logic is correct
- [ ] Handles file uploads securely (if applicable)

#### Example - Good Form Request:
```php
class StoreSurveyRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user() && auth()->user()->can('create', Survey::class);
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'in:draft,active,closed',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string|max:500',
            'questions.*.type' => 'required|in:text,radio,checkbox,select',
            'questions.*.options' => 'required_if:questions.*.type,radio,checkbox,select|array',
        ];
    }

    public function messages()
    {
        return [
            'questions.required' => 'At least one question is required',
            'questions.*.text.required' => 'Question text is required',
        ];
    }
}
```

### 3. API Resources ✅

#### Resource Classes
- [ ] Uses dedicated Resource classes for response formatting
- [ ] Conditional data inclusion based on user permissions
- [ ] Proper relationship loading
- [ ] Consistent date formatting
- [ ] No sensitive data exposure

#### Example - Good API Resource:
```php
class SurveyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'questions_count' => $this->whenLoaded('questions', $this->questions->count()),
            'responses_count' => $this->when(
                $request->user()?->isAdmin(),
                $this->responses_count
            ),
            'created_at' => $this->created_at->toISOString(),
            'created_by' => $this->whenLoaded('creator', [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
        ];
    }
}
```

### 4. Route Definition ✅

#### Route Organization
- [ ] Proper grouping and nesting
- [ ] Appropriate middleware application
- [ ] RESTful resource routes where applicable
- [ ] Rate limiting configured
- [ ] Consistent naming conventions

#### Example - Good Route Structure:
```php
Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/surveys', [SurveyController::class, 'index']);
    Route::get('/surveys/{survey}', [SurveyController::class, 'show']);
    
    // Authenticated routes
    Route::middleware(['api.auth', 'rate.limit:api,60,1'])->group(function () {
        Route::post('/survey-responses', [SurveyResponseController::class, 'store']);
        
        // Admin routes
        Route::middleware(['api.admin'])->prefix('admin')->group(function () {
            Route::apiResource('surveys', AdminSurveyController::class);
            Route::put('/surveys/{survey}/status', [AdminSurveyController::class, 'updateStatus']);
        });
    });
});
```

### 5. Database Queries ✅

#### Query Optimization
- [ ] Uses eager loading to prevent N+1 queries
- [ ] Proper indexing on frequently queried columns
- [ ] Pagination for large datasets
- [ ] Query scopes for reusable conditions
- [ ] Avoids unnecessary data retrieval

#### Example - Optimized Query:
```php
public function index(Request $request)
{
    $surveys = Survey::query()
        ->with(['creator:id,name', 'questions:id,survey_id,text,type'])
        ->withCount('responses')
        ->when($request->status, fn($q, $status) => $q->where('status', $status))
        ->when($request->search, function ($q, $search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate($this->getPerPage());

    return new SurveyCollection($surveys);
}
```

### 6. Testing ✅

#### Test Coverage
- [ ] Unit tests for models and business logic
- [ ] Feature tests for API endpoints
- [ ] Tests for authentication and authorization
- [ ] Edge case testing
- [ ] Error scenario testing

#### Example - Good Feature Test:
```php
class SurveyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_survey_with_questions()
    {
        $admin = User::factory()->admin()->create();
        $surveyData = [
            'title' => 'Test Survey',
            'description' => 'Test description',
            'status' => 'draft',
            'questions' => [
                [
                    'text' => 'What is your name?',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'text' => 'What is your age?',
                    'type' => 'radio',
                    'required' => true,
                    'options' => ['18-25', '26-35', '36-45', '46+'],
                ]
            ]
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/surveys', $surveyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'questions_count'
                ]
            ]);

        $this->assertDatabaseHas('surveys', [
            'title' => 'Test Survey',
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('survey_questions', 2);
    }

    public function test_regular_user_cannot_create_survey()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/surveys', [
                'title' => 'Test Survey',
            ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_for_invalid_survey_data()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/surveys', [
                'title' => '', // Invalid: empty title
                'questions' => [], // Invalid: no questions
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'questions']);
    }
}
```

### 7. Security ✅

#### Security Measures
- [ ] Input validation and sanitization
- [ ] SQL injection prevention (using Eloquent)
- [ ] XSS prevention in responses
- [ ] Proper authentication and authorization
- [ ] Rate limiting implementation
- [ ] No sensitive data in logs or responses

#### Example - Secure Implementation:
```php
public function show(Survey $survey, Request $request)
{
    // Authorization check
    if ($survey->status === 'draft' && !$request->user()?->isAdmin()) {
        return $this->forbiddenResponse('Access denied to draft surveys');
    }

    // Load relationships safely
    $survey->load(['questions' => function ($query) {
        $query->select('id', 'survey_id', 'text', 'type', 'options', 'required');
    }]);

    return $this->successResponse(
        new SurveyResource($survey),
        'Survey retrieved successfully'
    );
}
```

### 8. Documentation ✅

#### API Documentation
- [ ] OpenAPI/Swagger annotations
- [ ] Clear parameter descriptions
- [ ] Response examples
- [ ] Error response documentation
- [ ] Authentication requirements

#### Example - Good API Documentation:
```php
/**
 * @OA\Post(
 *     path="/api/v1/admin/surveys",
 *     summary="Create a new survey",
 *     description="Create a new survey with questions. Admin access required.",
 *     tags={"Admin - Surveys"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "questions"},
 *             @OA\Property(property="title", type="string", maxLength=255, example="Customer Satisfaction Survey"),
 *             @OA\Property(property="description", type="string", maxLength=1000, example="Survey to measure customer satisfaction"),
 *             @OA\Property(property="status", type="string", enum={"draft", "active", "closed"}, example="draft"),
 *             @OA\Property(
 *                 property="questions",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="text", type="string", example="How satisfied are you?"),
 *                     @OA\Property(property="type", type="string", enum={"text", "radio", "checkbox", "select"}),
 *                     @OA\Property(property="required", type="boolean", example=true),
 *                     @OA\Property(property="options", type="array", @OA\Items(type="string"))
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Survey created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Survey created successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/Survey")
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=403, description="Insufficient permissions")
 * )
 */
```

## Common Anti-Patterns to Avoid

### ❌ Bad Practices

1. **Fat Controllers**
```php
// BAD: Too much logic in controller
public function store(Request $request)
{
    // 50+ lines of validation, business logic, and data manipulation
    if (!$request->title) return response()->json(['error' => 'Title required'], 400);
    if (!$request->questions) return response()->json(['error' => 'Questions required'], 400);
    // ... more validation
    
    $survey = new Survey();
    $survey->title = $request->title;
    // ... many assignments
    $survey->save();
    
    foreach ($request->questions as $questionData) {
        // ... complex question creation logic
    }
    
    return response()->json(['survey' => $survey], 201);
}
```

2. **Inconsistent Response Format**
```php
// BAD: Different response formats
public function index() {
    return response()->json(['surveys' => $surveys]); // No success/message
}

public function store() {
    return response()->json(['success' => true, 'data' => $survey]); // Different structure
}
```

3. **Missing Validation**
```php
// BAD: No validation
public function store(Request $request)
{
    $survey = Survey::create($request->all()); // Unsafe mass assignment
    return $survey;
}
```

4. **N+1 Query Problems**
```php
// BAD: Will cause N+1 queries
public function index()
{
    $surveys = Survey::all();
    
    foreach ($surveys as $survey) {
        $survey->questions_count = $survey->questions()->count(); // N+1 query
    }
    
    return $surveys;
}
```

### ✅ Good Practices

1. **Thin Controllers with Service Classes**
```php
// GOOD: Delegating to service classes
public function store(StoreSurveyRequest $request)
{
    $survey = $this->surveyService->create($request->validated());
    
    return $this->successResponse(
        new SurveyResource($survey),
        'Survey created successfully',
        201
    );
}
```

2. **Consistent Response Format**
```php
// GOOD: Using ApiResponseTrait for consistency
public function index()
{
    $surveys = Survey::with('questions')->paginate();
    return $this->paginatedResponse($surveys, 'Surveys retrieved successfully');
}

public function store(StoreSurveyRequest $request)
{
    $survey = Survey::create($request->validated());
    return $this->successResponse($survey, 'Survey created successfully', 201);
}
```

## Performance Checklist

### Database Performance
- [ ] Proper indexing on foreign keys and frequently queried columns
- [ ] Use of `select()` to limit retrieved columns
- [ ] Eager loading relationships with `with()`
- [ ] Query optimization using `explain` for complex queries
- [ ] Pagination for large datasets

### Caching Strategy
- [ ] Cache frequently accessed, rarely changed data
- [ ] Use appropriate cache keys and TTL
- [ ] Implement cache invalidation strategy
- [ ] Consider using Redis for session storage

### Response Optimization
- [ ] Minimize response payload size
- [ ] Use API resources to control data exposure
- [ ] Implement HTTP caching headers where appropriate
- [ ] Consider response compression

## Deployment Considerations

### Environment Configuration
- [ ] Environment-specific configuration files
- [ ] Secure storage of API keys and secrets
- [ ] Proper logging configuration
- [ ] Error reporting setup

### Monitoring & Logging
- [ ] API endpoint monitoring
- [ ] Performance metrics tracking
- [ ] Error logging and alerting
- [ ] Rate limiting monitoring

This checklist ensures consistent, secure, and performant API development across the team. Use it for self-review before submitting code and during peer code reviews.