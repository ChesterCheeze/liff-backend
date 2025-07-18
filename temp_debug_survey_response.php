<?php

// Quick debug script to test survey response creation
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;

try {
    echo "Testing survey response creation...\n";

    // Get first user and survey
    $user = User::first();
    $survey = Survey::first();

    if (! $user) {
        echo "No users found\n";
        exit(1);
    }

    if (! $survey) {
        echo "No surveys found\n";
        exit(1);
    }

    echo "User ID: {$user->id}, User type: ".get_class($user)."\n";
    echo "Survey ID: {$survey->id}\n";

    // Test creating survey response
    $responseData = [
        'survey_id' => $survey->id,
        'form_data' => ['question_1' => 'test answer'],
        'completed_at' => now(),
        'user_id' => $user->id,
        'user_type' => get_class($user),
    ];

    echo "Creating survey response with data:\n";
    print_r($responseData);

    $response = SurveyResponse::create($responseData);
    echo "Survey response created successfully with ID: {$response->id}\n";

    // Test loading the response with survey
    $response->load('survey');
    echo "Survey relationship loaded successfully\n";

    // Test the resource
    $resource = new \App\Http\Resources\V1\SurveyResponseResource($response);
    $array = $resource->toArray(new \Illuminate\Http\Request());
    echo "Resource conversion successful\n";
    print_r($array);

} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
