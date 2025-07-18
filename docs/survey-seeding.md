# Survey and Survey Response Seeding

This documentation explains how to use the survey and survey response seeders to populate your database with realistic test data for development and testing purposes.

## Overview

The seeding system consists of two main seeders:
- **SurveySeeder**: Creates surveys with questions
- **SurveyResponseSeeder**: Creates realistic survey responses

## Quick Start

### Run All Seeders
```bash
php artisan db:seed
```

### Run Specific Seeders
```bash
# Run only survey seeder
php artisan db:seed --class=SurveySeeder

# Run only survey response seeder
php artisan db:seed --class=SurveyResponseSeeder
```

### Fresh Database with Seeders
```bash
php artisan migrate:fresh --seed
```

## SurveySeeder

### What It Creates

The SurveySeeder creates 5 different surveys with realistic questions:

1. **Product Satisfaction Survey** (Active)
   - Section: Customer Feedback
   - Questions: Rating, text input, radio buttons
   - Purpose: Gather customer feedback on products

2. **Customer Demographics Survey** (Active)
   - Section: Market Research
   - Questions: Radio buttons, select dropdown, checkboxes
   - Purpose: Understand customer demographics

3. **Webinar Experience Survey** (Draft)
   - Section: Event Feedback
   - Questions: Rating, textarea, radio buttons
   - Purpose: Collect feedback on webinar events

4. **Workplace Satisfaction Survey** (Active)
   - Section: Employee Feedback
   - Questions: Rating, textarea, radio buttons
   - Purpose: Anonymous employee satisfaction tracking

5. **Feature Request Survey** (Inactive)
   - Section: Product Development
   - Questions: Checkboxes, textarea, radio buttons
   - Purpose: Prioritize new feature development

### Survey Structure

Each survey includes:
- **Basic Information**: section, name, description, status
- **Questions**: Various question types with appropriate options
- **Question Types**: rating, text, textarea, radio, select, checkbox
- **Validation**: Required/optional field settings

### Question Types Example

```php
// Rating question (1-5 scale)
[
    'question_text' => 'How would you rate your overall satisfaction?',
    'question_type' => 'rating',
    'required' => true,
    'options' => json_encode(['1', '2', '3', '4', '5']),
]

// Multiple choice (radio)
[
    'question_text' => 'Would you recommend our product?',
    'question_type' => 'radio',
    'required' => true,
    'options' => json_encode(['Yes', 'No', 'Maybe']),
]

// Multiple selection (checkbox)
[
    'question_text' => 'Which features interest you?',
    'question_type' => 'checkbox',
    'required' => false,
    'options' => json_encode(['Feature A', 'Feature B', 'Feature C']),
]
```

## SurveyResponseSeeder

### What It Creates

The SurveyResponseSeeder creates:
- **LineOA Users**: 10 fake users if none exist
- **Realistic Responses**: Sample responses for each survey
- **Additional Random Responses**: 15 extra responses using factories
- **Varied Timestamps**: Responses spread across different dates

### Response Data Structure

Responses include realistic answers matching question types:

```php
// Example response data
[
    'form_data' => json_encode([
        'How would you rate your overall satisfaction?' => '5',
        'What features do you find most valuable?' => 'User interface and reporting',
        'Would you recommend our product?' => 'Yes'
    ]),
    'completed_at' => now()->subDays(5),
]
```

### Sample Response Content

The seeder includes realistic response content:
- **Product feedback**: "The user interface is very intuitive and the reporting features are excellent."
- **Demographics**: Age ranges, referral sources, industry selections
- **Event feedback**: Detailed suggestions for future webinars
- **Employee feedback**: Work-life balance and job satisfaction comments

## Dependencies

### Required Models
- `Survey`: Main survey model
- `SurveyQuestion`: Individual questions within surveys
- `SurveyResponse`: User responses to surveys
- `LineOAUser`: Users who complete surveys

### Required Factories
- `LineOAUserFactory`: Creates fake LINE OA users
- `SurveyResponseFactory`: Creates random survey responses

## Database Schema Requirements

### Surveys Table
```sql
- id (primary key)
- section (string)
- name (string)
- description (text)
- status (string: active/draft/inactive)
- created_at, updated_at (timestamps)
```

### Survey Questions Table
```sql
- id (primary key)
- survey_id (foreign key)
- question_text (text)
- question_type (string)
- required (boolean)
- options (json, nullable)
- created_at, updated_at (timestamps)
```

### Survey Responses Table
```sql
- id (primary key)
- line_id (foreign key to LineOAUser)
- survey_id (foreign key to Survey)
- form_data (json)
- completed_at (timestamp, nullable)
- created_at, updated_at (timestamps)
```

## Customization

### Adding New Surveys

To add new surveys to the seeder, modify the `$surveys` array in `SurveySeeder.php`:

```php
[
    'section' => 'Your Section',
    'name' => 'Your Survey Name',
    'description' => 'Survey description',
    'status' => 'active', // active, draft, or inactive
    'questions' => [
        [
            'question_text' => 'Your question?',
            'question_type' => 'radio', // rating, text, textarea, radio, select, checkbox
            'required' => true,
            'options' => json_encode(['Option 1', 'Option 2']), // null for text/textarea
        ],
        // Add more questions...
    ]
]
```

### Adding New Response Data

To add new response patterns, modify the `$responseData` array in `SurveyResponseSeeder.php`:

```php
[
    'survey_name' => 'Your Survey Name',
    'responses' => [
        [
            'form_data' => json_encode([
                'Question text?' => 'Answer text',
                'Another question?' => 'Another answer'
            ]),
            'completed_at' => now()->subDays(7),
        ],
        // Add more responses...
    ]
]
```

## Troubleshooting

### Common Issues

1. **Foreign Key Constraints**
   ```bash
   # Ensure migrations are run before seeding
   php artisan migrate
   php artisan db:seed
   ```

2. **Missing LineOA Users**
   - The SurveyResponseSeeder automatically creates users if none exist
   - Or run: `php artisan db:seed --class=LineOAUserSeeder`

3. **Duplicate Data**
   ```bash
   # Clear database and reseed
   php artisan migrate:fresh --seed
   ```

4. **Seeder Not Found**
   ```bash
   # Regenerate autoload files
   composer dump-autoload
   ```

### Verification Commands

Check seeded data:
```bash
# Count surveys
php artisan tinker
>>> App\Models\Survey::count()

# Count responses
>>> App\Models\SurveyResponse::count()

# View survey with questions
>>> App\Models\Survey::with('questions')->first()
```

## Development Workflow

### Typical Usage

1. **Fresh Development Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Add New Test Data**
   ```bash
   php artisan db:seed --class=SurveySeeder
   ```

3. **Reset Response Data**
   ```bash
   # Truncate responses and reseed
   php artisan tinker
   >>> App\Models\SurveyResponse::truncate()
   >>> exit
   php artisan db:seed --class=SurveyResponseSeeder
   ```

4. **Testing with Fresh Data**
   ```bash
   php artisan migrate:fresh --seed
   php artisan test
   ```

## Integration with Testing

The seeders work well with Laravel's testing framework:

```php
// In your test setup
public function setUp(): void
{
    parent::setUp();
    $this->seed([
        SurveySeeder::class,
        SurveyResponseSeeder::class,
    ]);
}
```

## Performance Considerations

- **Survey Creation**: Creates 5 surveys with 15 total questions
- **Response Creation**: Creates ~30 realistic responses + 15 random responses
- **Execution Time**: Typically completes in 2-5 seconds
- **Memory Usage**: Minimal impact, suitable for development environments

## Security Notes

- All seeded data is for development/testing only
- Contains no real user information
- Safe to use in development and staging environments
- **Never run seeders in production** without careful consideration