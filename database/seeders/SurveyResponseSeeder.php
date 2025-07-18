<?php

namespace Database\Seeders;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\Seeder;

class SurveyResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some LineOA users first if they don't exist
        if (LineOAUser::count() === 0) {
            LineOAUser::factory(10)->create();
        }

        $surveys = Survey::all();
        $users = LineOAUser::all();

        if ($surveys->isEmpty() || $users->isEmpty()) {
            return;
        }

        $responseData = [
            // Product Satisfaction Survey responses
            [
                'survey_name' => 'Product Satisfaction Survey',
                'responses' => [
                    [
                        'form_data' => json_encode([
                            'How would you rate your overall satisfaction with our product?' => '5',
                            'What features do you find most valuable?' => 'The user interface is very intuitive and the reporting features are excellent.',
                            'Would you recommend our product to a friend?' => 'Yes',
                        ]),
                        'completed_at' => now()->subDays(5),
                    ],
                    [
                        'form_data' => json_encode([
                            'How would you rate your overall satisfaction with our product?' => '4',
                            'What features do you find most valuable?' => 'Integration capabilities and customer support.',
                            'Would you recommend our product to a friend?' => 'Yes',
                        ]),
                        'completed_at' => now()->subDays(10),
                    ],
                    [
                        'form_data' => json_encode([
                            'How would you rate your overall satisfaction with our product?' => '3',
                            'What features do you find most valuable?' => 'Price point is reasonable.',
                            'Would you recommend our product to a friend?' => 'Maybe',
                        ]),
                        'completed_at' => now()->subDays(15),
                    ],
                ],
            ],
            // Customer Demographics Survey responses
            [
                'survey_name' => 'Customer Demographics Survey',
                'responses' => [
                    [
                        'form_data' => json_encode([
                            'What is your age range?' => '26-35',
                            'How did you hear about us?' => 'Google Search',
                            'What industries are you involved in? (Select all that apply)' => ['Technology', 'Finance'],
                        ]),
                        'completed_at' => now()->subDays(7),
                    ],
                    [
                        'form_data' => json_encode([
                            'What is your age range?' => '36-45',
                            'How did you hear about us?' => 'Friend Referral',
                            'What industries are you involved in? (Select all that apply)' => ['Healthcare', 'Education'],
                        ]),
                        'completed_at' => now()->subDays(12),
                    ],
                    [
                        'form_data' => json_encode([
                            'What is your age range?' => '18-25',
                            'How did you hear about us?' => 'Social Media',
                            'What industries are you involved in? (Select all that apply)' => ['Technology', 'Retail'],
                        ]),
                        'completed_at' => now()->subDays(20),
                    ],
                ],
            ],
            // Webinar Experience Survey responses
            [
                'survey_name' => 'Webinar Experience Survey',
                'responses' => [
                    [
                        'form_data' => json_encode([
                            'How would you rate the overall quality of the webinar?' => '5',
                            'What topics would you like to see covered in future webinars?' => 'Advanced analytics, API integrations, and best practices for data visualization.',
                            'How likely are you to attend future webinars?' => 'Very likely',
                        ]),
                        'completed_at' => now()->subDays(3),
                    ],
                    [
                        'form_data' => json_encode([
                            'How would you rate the overall quality of the webinar?' => '4',
                            'What topics would you like to see covered in future webinars?' => 'More hands-on workshops and Q&A sessions.',
                            'How likely are you to attend future webinars?' => 'Likely',
                        ]),
                        'completed_at' => now()->subDays(8),
                    ],
                ],
            ],
            // Workplace Satisfaction Survey responses
            [
                'survey_name' => 'Workplace Satisfaction Survey',
                'responses' => [
                    [
                        'form_data' => json_encode([
                            'How satisfied are you with your current role?' => '4',
                            'What aspects of your job do you enjoy most?' => 'Collaborative team environment and opportunities for professional growth.',
                            'How would you rate work-life balance?' => 'Good',
                        ]),
                        'completed_at' => now()->subDays(6),
                    ],
                    [
                        'form_data' => json_encode([
                            'How satisfied are you with your current role?' => '5',
                            'What aspects of your job do you enjoy most?' => 'Flexible working arrangements and meaningful projects.',
                            'How would you rate work-life balance?' => 'Excellent',
                        ]),
                        'completed_at' => now()->subDays(14),
                    ],
                    [
                        'form_data' => json_encode([
                            'How satisfied are you with your current role?' => '3',
                            'What aspects of your job do you enjoy most?' => 'Learning new technologies.',
                            'How would you rate work-life balance?' => 'Fair',
                        ]),
                        'completed_at' => now()->subDays(21),
                    ],
                ],
            ],
        ];

        foreach ($responseData as $surveyResponseGroup) {
            $survey = $surveys->where('name', $surveyResponseGroup['survey_name'])->first();

            if (! $survey) {
                continue;
            }

            foreach ($surveyResponseGroup['responses'] as $responseInfo) {
                $randomUser = $users->random();

                SurveyResponse::create([
                    'line_id' => $randomUser->id,
                    'survey_id' => $survey->id,
                    'form_data' => $responseInfo['form_data'],
                    'completed_at' => $responseInfo['completed_at'],
                    'created_at' => $responseInfo['completed_at'],
                    'updated_at' => $responseInfo['completed_at'],
                ]);
            }
        }

        // Create some additional random responses using factories
        SurveyResponse::factory(15)->create([
            'survey_id' => $surveys->random()->id,
            'line_id' => $users->random()->id,
        ]);
    }
}
