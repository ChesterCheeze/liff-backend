# API Development Workshop: Practical Exercises

## Workshop Overview
This workshop provides hands-on exercises for junior developers to learn Laravel API development through practical implementation. Each exercise builds upon the previous one, creating a complete learning experience.

> 🚀 **Workshop Philosophy**: "I hear and I forget. I see and I remember. I do and I understand." - Let's get our hands dirty with code!

## Setup Instructions

### Prerequisites
1. Laravel development environment set up
2. Database configured and migrated
3. Basic understanding of PHP and Laravel concepts

> 🔧 **Environment Check**: Run `php artisan --version` to make sure Laravel is ready. If you see a version number, you're good to go!

### Workshop Structure
- **Duration**: 2-3 weeks (part-time)
- **Format**: Progressive exercises with code reviews
- **Goal**: Build a complete API feature from scratch

> 📈 **Learning Curve**: Don't worry if Exercise 1 feels overwhelming - by Exercise 5, you'll be writing APIs like a pro!

## Exercise 1: Build Your First API Endpoint (Day 1-2)

### Objective
Create a simple read-only API endpoint for listing categories.

### Step-by-Step Implementation

#### Step 1: Create the Migration
```bash
php artisan make:migration create_categories_table
```

> 🏗️ **Migration Magic**: Think of migrations as blueprints for your database. You wouldn't build a house without blueprints, so don't build a database without migrations!

```php
// database/migrations/xxxx_create_categories_table.php
public function up()
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
});
```

> 🎭 **Route Hierarchy**: Notice how routes are nested? Public routes are outside the auth middleware, protected routes are inside. It's like having different security zones in an office building!

> 💡 **Database Design Tip**: `id()` creates an auto-incrementing primary key. Laravel gives you this for free - it's like getting extra fries with your order!

> 🔍 **Index Insight**: `unique()` on name prevents duplicate categories. It's like having a bouncer who remembers every face!

#### Step 2: Create the Model
```bash
php artisan make:model Category
```

```php
// app/Models/Category.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

> 🎯 **Scope Superpower**: Query scopes are like having custom filters for your data. `Category::active()->get()` is much cleaner than repeating the where clause everywhere!

> 🛡️ **Mass Assignment Protection**: `$fillable` is your security guard - only these fields can be mass-assigned. It prevents sneaky data from slipping through!

#### Step 3: Create the Controller
```bash
php artisan make:controller Api/V1/CategoryController
```

> 🏗️ **Folder Structure**: Notice the `Api/V1` path? This creates a nice hierarchy: `app/Http/Controllers/Api/V1/CategoryController.php`. Organization is key!

```php
// app/Http/Controllers/Api/V1/CategoryController.php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="List categories",
     *     description="Get list of categories with optional filtering",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         @OA\Schema(type="boolean"),
     *         description="Filter by active status"
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Search in category name and description"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $this->getPerPage();
        
        $categories = Category::query()
            ->when($request->has('is_active'), function ($query) use ($request) {
                return $query->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->paginatedResponse($categories, 'Categories retrieved successfully');
    }
}
```

> 🔍 **Query Builder Wizardry**: The `when()` method is Laravel's way of saying "only add this condition if the value exists." No more ugly nested if statements!

> 📚 **OpenAPI Documentation**: Those `@OA` comments aren't just decorative - they generate beautiful API documentation automatically. It's like having a technical writer as your coding buddy!

> 🔄 **Pagination Pro Tip**: Always paginate list endpoints. Loading 10,000 records at once is like trying to drink the ocean - technically possible, but not recommended!

#### Step 4: Add Routes
```php
// In routes/api.php, add to the v1 group:
Route::prefix('v1')->group(function () {
    // ... existing routes
    
    // Public category routes
    Route::get('/categories', [CategoryController::class, 'index']);
});
```

> 🚦 **Route Grouping**: Route groups are like organizing your closet - everything has its place and purpose. The `prefix('v1')` means all routes start with `/api/v1/`.

#### Step 5: Create Factory and Seeder
```bash
php artisan make:factory CategoryFactory
php artisan make:seeder CategorySeeder
```

```php
// database/factories/CategoryFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    public function active()
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive()
    {
        return $this->state(['is_active' => false]);
    }
}
```

> 🎲 **Factory States**: States are like different flavors of the same ice cream. The base factory is vanilla, and states add the chocolate chips and sprinkles!

> 🎯 **Realistic Data**: `$this->faker->words(2, true)` creates 2-word category names like "Product Development" - much better than "Lorem Ipsum Category"!

```php
// database/seeders/CategorySeeder.php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::factory()->count(20)->create();
        
        // Create some specific categories
        Category::create([
            'name' => 'Technology',
            'description' => 'Technology related surveys',
            'is_active' => true,
        ]);
        
        Category::create([
            'name' => 'Health',
            'description' => 'Health and wellness surveys',
            'is_active' => true,
        ]);
    }
}
```

#### Step 6: Test Your Implementation
```bash
# Run migrations and seed data
php artisan migrate
php artisan db:seed --class=CategorySeeder

# Test the endpoint
curl -X GET "http://localhost:8000/api/v1/categories" \
     -H "Accept: application/json"

# Test with filters
curl -X GET "http://localhost:8000/api/v1/categories?is_active=true&search=tech" \
     -H "Accept: application/json"
```

> 🧪 **Testing Philosophy**: Always test the happy path first, then break things intentionally. If your API can handle chaos, it can handle anything!

> 📱 **Header Hint**: The `Accept: application/json` header tells Laravel you want JSON responses. Without it, you might get HTML error pages - yuck!

### Expected Outcome
- Understand basic API controller structure
- Learn about query filtering and pagination
- Practice using factories and seeders

> 🎓 **Learning Checkpoint**: If you can explain to someone else how the `when()` method works, you've mastered this exercise!

## Exercise 2: Add Authentication and Authorization (Day 3-4)

### Objective
Protect your API endpoints with authentication and add user context.

> 🔐 **Security First**: Authentication is like checking IDs at a club - you need to know who's trying to get in before you let them access the VIP area!

#### Step 1: Protect Routes with Authentication
```php
// In routes/api.php
Route::prefix('v1')->group(function () {
    // Public routes (no auth required)
    Route::get('/categories', [CategoryController::class, 'index']);
    
    // Authenticated routes
    Route::middleware(['api.auth'])->group(function () {
        Route::get('/categories/my-favorites', [CategoryController::class, 'favorites']);
    });
});
```

#### Step 2: Add User Context Methods
```php
// Add to CategoryController
public function favorites(Request $request)
{
    $user = $this->getCurrentUser();
    
    // Assuming we have a user-category relationship for favorites
    $categories = $user->favoriteCategories()
        ->with('surveys')
        ->paginate($this->getPerPage());
    
    return $this->successResponse([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
        ],
        'categories' => $categories->items(),
        'meta' => [
            'total_favorites' => $user->favoriteCategories()->count(),
        ]
    ], 'User favorite categories retrieved successfully');
}
```

> 👤 **User Context Magic**: `$this->getCurrentUser()` is like having a digital name tag - you always know who you're talking to in your API methods!

> 📊 **Meta Information**: Adding `meta` data to responses is like including a nutrition label - it gives extra context without cluttering the main data.

#### Step 3: Write Authentication Tests
```php
// tests/Feature/CategoryTest.php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories_without_authentication()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'description', 'is_active']
                ],
                'pagination'
            ]);
    }

    public function test_can_filter_categories_by_active_status()
    {
        Category::factory()->active()->count(2)->create();
        Category::factory()->inactive()->count(1)->create();

        $response = $this->getJson('/api/v1/categories?is_active=true');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_requires_authentication_for_favorites()
    {
        $response = $this->getJson('/api/v1/categories/my-favorites');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_favorites()
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/categories/my-favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'categories',
                    'meta'
                ]
            ]);
    }
}
```

> 🧪 **Test Structure Magic**: `assertJsonStructure()` is like checking if your JSON response has the right skeleton - it doesn't care about the meat, just the bones!

> 🎭 **Acting as User**: `actingAs($user, 'sanctum')` is like method acting for tests - your test temporarily becomes that user for the duration of the request.
> 📦 **Seeder Strategy**: Create both random data (for variety) and specific data (for testing known scenarios). It's like having both surprise and birthday parties - you need both!
## Exercise 3: Implement Full CRUD Operations (Day 5-7)

### Objective
Add create, update, and delete operations with proper validation.

> 🛠️ **CRUD Philosophy**: Create, Read, Update, Delete - the four horsemen of data management. Master these and you can build any API!

#### Step 1: Create Form Request Classes
```bash
php artisan make:request V1/StoreCategoryRequest
php artisan make:request V1/UpdateCategoryRequest
```

```php
// app/Http/Requests/V1/StoreCategoryRequest.php
<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize()
    {
        // Only admins can create categories
        return auth()->user() && auth()->user()->isAdmin();
    }
```

> 🚪 **Authorization Gate**: The `authorize()` method is like having a bouncer who checks not just your ID, but also your VIP status!

> 🔒 **Security Layer**: Form Requests handle both validation AND authorization in one place. It's like having a Swiss Army knife for request security!

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'A category with this name already exists',
            'name.max' => 'Category name cannot exceed 255 characters',
        ];
    }
}
```

> 📝 **Custom Messages**: Default validation messages are like generic greeting cards - they work, but custom messages are like handwritten notes that show you care!

> 🎯 **Validation Chaining**: `required|string|max:255|unique:categories,name` reads like a checklist - each rule must pass for the validation to succeed.

```php
// app/Http/Requests/V1/UpdateCategoryRequest.php
<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user() && auth()->user()->isAdmin();
    }

    public function rules()
    {
        $categoryId = $this->route('category')->id;
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId)
            ],
            'description' => 'sometimes|nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
```

> 🔄 **Update vs Create**: Notice `sometimes` in update rules? It means "only validate if the field is present" - perfect for partial updates!

> 🚫 **Unique Rule Magic**: `Rule::unique('categories', 'name')->ignore($categoryId)` says "this name must be unique, but ignore the current record" - preventing false duplicates on updates!

#### Step 2: Implement CRUD Methods
```php
// Add to CategoryController
use App\Http\Requests\V1\StoreCategoryRequest;
use App\Http\Requests\V1\UpdateCategoryRequest;

/**
 * @OA\Post(
 *     path="/api/v1/categories",
 *     summary="Create a new category",
 *     tags={"Categories"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Technology"),
 *             @OA\Property(property="description", type="string", example="Tech related surveys"),
 *             @OA\Property(property="is_active", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Category created successfully"),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=403, description="Insufficient permissions")
 * )
 */
public function store(StoreCategoryRequest $request)
{
    $category = Category::create($request->validated());
    
    return $this->successResponse(
        null,
        'Category deleted successfully'
    );
}
```

> 🗑️ **Soft Delete Wisdom**: Check for relationships before deleting! You don't want to be the person who accidentally removes a load-bearing wall.

> ⚖️ **HTTP 409**: Status code 409 means "Conflict" - perfect for when business rules prevent an action, like deleting a category with surveys.

> 🔄 **Fresh Data**: `$category->fresh()` reloads the model from the database - like hitting F5 to see the latest changes!

> ✨ **HTTP Status Codes**: `201` means "Created" - it's like saying "Welcome to the party!" to your new resource.

> 🛡️ **Validated Data Only**: `$request->validated()` ensures only clean, approved data makes it to your database - like having a quality control inspector!

#### Step 3: Update Routes for Full CRUD
```php
// In routes/api.php
Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    
    // Authenticated routes
    Route::middleware(['api.auth'])->group(function () {
        Route::get('/categories/my-favorites', [CategoryController::class, 'favorites']);
        
        // Admin-only routes
        Route::middleware(['api.admin'])->group(function () {
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        });
    });
});
```

> 🏰 **Middleware Castle**: Routes are protected by multiple layers of middleware like a medieval castle - you need to pass through authentication, then admin authorization to reach the treasure (admin endpoints)!

#### Step 4: Add Comprehensive Tests
```php
// Add to CategoryTest.php

public function test_admin_can_create_category()
{
    $admin = User::factory()->admin()->create();
    
    $categoryData = [
        'name' => 'Test Category',
        'description' => 'Test description',
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/categories', $categoryData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'description', 'is_active']
        ]);
        
    $this->assertDatabaseHas('categories', $categoryData);
}
```

> 🗄️ **Database Assertions**: `assertDatabaseHas()` is like checking if your item really made it into the shopping cart - it verifies the data actually got saved!

## Exercise 4: Add API Resources and Collections (Day 8-9)

### Objective
Create proper API resource transformers for consistent data formatting.

> 🎭 **Transformation Theatre**: API Resources are like costume designers - they take your raw model (the actor) and dress it up for the public performance (API response)!

```bash
php artisan make:resource V1/CategoryResource
php artisan make:resource V1/CategoryCollection
```

```php
// app/Http/Resources/V1/CategoryResource.php
<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'surveys_count' => $this->when(
                $this->relationLoaded('surveys'),
                $this->surveys->count()
            ),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Include additional data for admin users
            'admin_data' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                [
                    'total_responses' => $this->surveys()->withCount('responses')->sum('responses_count'),
                    'last_survey_created' => $this->surveys()->latest()->first()?->created_at,
                ]
            ),
        ];
    }
}
```

> 🎫 **VIP Access**: `$this->when()` is like having VIP sections in your API - some data is only for special users!

> 📅 **Date Formatting**: `->toISOString()` ensures consistent date format across all responses. It's like having a universal translator for dates!

```php
// app/Http/Resources/V1/CategoryCollection.php
<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => CategoryResource::collection($this->collection),
            'meta' => [
                'total_categories' => $this->collection->count(),
                'active_categories' => $this->collection->where('is_active', true)->count(),
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Categories retrieved successfully',
        ];
    }
}
```

> 📦 **Collection vs Resource**: Collections handle lists, Resources handle individual items. It's like the difference between a shipping box (collection) and gift wrapping (resource)!

> 📊 **Meta Magic**: Adding meta information to collections provides context about the data set without cluttering individual items.

## Exercise 5: Performance Optimization (Day 10-11)

### Objective
Optimize queries and implement caching strategies.

> ⚡ **Performance Philosophy**: "Make it work, make it right, make it fast" - but smart developers think about performance from the start!

```php
// Add to CategoryController

public function index(Request $request)
{
    $cacheKey = 'categories:' . md5($request->getQueryString());
    $cacheTtl = 300; // 5 minutes
    
    $categories = Cache::remember($cacheKey, $cacheTtl, function () use ($request) {
        return Category::query()
            ->with(['surveys' => function ($query) {
                $query->select('id', 'category_id', 'title', 'status')
                      ->where('status', 'active');
            }])
            ->when($request->has('is_active'), function ($query) use ($request) {
                return $query->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());
    });

    return new CategoryCollection($categories);
}
```

> 🗃️ **Caching Strategy**: Cache keys should be unique and descriptive. `md5($request->getQueryString())` ensures different filter combinations get different cache entries!

> ⏰ **TTL Wisdom**: 5 minutes (300 seconds) is a good balance for most data - fresh enough to be relevant, long enough to reduce database hits.

## Workshop Assessment

### Final Project: Mini API Feature

**Task**: Implement a complete "Tags" feature for surveys with the following requirements:

> 🏆 **Final Boss Challenge**: This is where you prove you've mastered the fundamentals. Think of it as your API development graduation exam!

1. **Models & Migrations**
   - Create Tag model with name, slug, color fields
   - Create pivot table for survey-tag relationship

> 🎨 **Design Thinking**: Tags are like labels on file folders - they help organize and categorize content for easy finding later!

2. **API Endpoints**
   - `GET /api/v1/tags` - List all tags
   - `POST /api/v1/tags` - Create tag (admin only)
   - `GET /api/v1/tags/{tag}` - Show tag with associated surveys
   - `PUT /api/v1/tags/{tag}` - Update tag (admin only)
   - `DELETE /api/v1/tags/{tag}` - Delete tag (admin only)
   - `POST /api/v1/surveys/{survey}/tags` - Attach tags to survey
   - `DELETE /api/v1/surveys/{survey}/tags/{tag}` - Remove tag from survey

> 🔗 **Relationship Endpoints**: These nested endpoints follow REST principles for managing relationships between resources!

3. **Requirements**
   - Use proper validation
   - Implement API resources
   - Add comprehensive tests
   - Include API documentation
   - Implement caching
   - Add rate limiting

4. **Bonus Features**
   - Tag color validation (hex colors)
   - Tag usage statistics
   - Auto-generate slugs from names
   - Search functionality

> 🌟 **Bonus Round**: These features separate good developers from great ones. They show you think about user experience and data integrity!

### Success Criteria
- All endpoints work correctly
- Proper error handling
- Good test coverage (>80%)
- Clean, readable code
- Proper documentation

> 🎯 **Quality Checklist**: Great code isn't just code that works - it's code that works, is tested, is documented, and makes sense to other developers!

This workshop provides a complete learning path from basic API development to advanced features, ensuring junior developers gain practical experience with real-world Laravel API patterns.

> 🚀 **Graduation Day**: If you can complete this workshop, you're ready to contribute meaningfully to any Laravel API project. Welcome to the club!