<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surveys = [
            [
                'section' => 'Customer Feedback',
                'name' => 'Product Satisfaction Survey',
                'description' => 'Help us understand your experience with our products and services.',
                'status' => 'active',
                'questions' => [
                    [
                        'label' => 'How would you rate your overall satisfaction with our product?',
                        'name' => 'satisfaction_rating',
                        'type' => 'rating',
                        'required' => true,
                    ],
                    [
                        'label' => 'What features do you find most valuable?',
                        'name' => 'valuable_features',
                        'type' => 'text',
                        'required' => false,
                    ],
                    [
                        'label' => 'Would you recommend our product to a friend?',
                        'name' => 'recommend_product',
                        'type' => 'radio',
                        'required' => true,
                    ],
                ],
            ],
            [
                'section' => 'Market Research',
                'name' => 'Customer Demographics Survey',
                'description' => 'Help us better understand our customer base and improve our services.',
                'status' => 'active',
                'questions' => [
                    [
                        'label' => 'What is your age range?',
                        'name' => 'age_range',
                        'type' => 'radio',
                        'required' => true,
                    ],
                    [
                        'label' => 'How did you hear about us?',
                        'name' => 'how_heard',
                        'type' => 'select',
                        'required' => true,
                    ],
                    [
                        'label' => 'What industries are you involved in? (Select all that apply)',
                        'name' => 'industries',
                        'type' => 'checkbox',
                        'required' => false,
                    ],
                ],
            ],
            [
                'section' => 'Event Feedback',
                'name' => 'Webinar Experience Survey',
                'description' => 'Share your thoughts about our recent webinar and help us improve future events.',
                'status' => 'draft',
                'questions' => [
                    [
                        'label' => 'How would you rate the overall quality of the webinar?',
                        'name' => 'webinar_quality_rating',
                        'type' => 'rating',
                        'required' => true,
                    ],
                    [
                        'label' => 'What topics would you like to see covered in future webinars?',
                        'name' => 'future_topics',
                        'type' => 'textarea',
                        'required' => false,
                    ],
                    [
                        'label' => 'How likely are you to attend future webinars?',
                        'name' => 'future_attendance',
                        'type' => 'radio',
                        'required' => true,
                    ],
                ],
            ],
            [
                'section' => 'Employee Feedback',
                'name' => 'Workplace Satisfaction Survey',
                'description' => 'Anonymous survey to understand employee satisfaction and workplace culture.',
                'status' => 'active',
                'questions' => [
                    [
                        'label' => 'How satisfied are you with your current role?',
                        'name' => 'role_satisfaction',
                        'type' => 'rating',
                        'required' => true,
                    ],
                    [
                        'label' => 'What aspects of your job do you enjoy most?',
                        'name' => 'job_enjoyment',
                        'type' => 'textarea',
                        'required' => false,
                    ],
                    [
                        'label' => 'How would you rate work-life balance?',
                        'name' => 'work_life_balance',
                        'type' => 'radio',
                        'required' => true,
                    ],
                ],
            ],
            [
                'section' => 'Product Development',
                'name' => 'Feature Request Survey',
                'description' => 'Help us prioritize new features and improvements for our platform.',
                'status' => 'inactive',
                'questions' => [
                    [
                        'label' => 'Which new features would be most valuable to you?',
                        'name' => 'valuable_features',
                        'type' => 'checkbox',
                        'required' => true,
                    ],
                    [
                        'label' => 'Describe any specific functionality you need',
                        'name' => 'specific_functionality',
                        'type' => 'textarea',
                        'required' => false,
                    ],
                    [
                        'label' => 'How important is mobile access to you?',
                        'name' => 'mobile_importance',
                        'type' => 'radio',
                        'required' => true,
                    ],
                ],
            ],
        ];

        foreach ($surveys as $surveyData) {
            $questions = $surveyData['questions'];
            unset($surveyData['questions']);

            $survey = Survey::create($surveyData);

            foreach ($questions as $questionData) {
                $questionData['survey_id'] = $survey->id;
                SurveyQuestion::create($questionData);
            }
        }
    }
}
