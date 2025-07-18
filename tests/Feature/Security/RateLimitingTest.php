<?php

namespace Tests\Feature\Security;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    private $user;

    private $lineUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->lineUser = LineOAUser::factory()->create();

        Cache::flush();
        RateLimiter::clear('*');
    }

    public function test_authentication_endpoints_have_strict_rate_limiting()
    {
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/auth/admin/login', $loginData);

            if ($i < 4) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->postJson('/api/v1/auth/admin/login', $loginData);
        $response->assertStatus(429);
    }

    public function test_public_survey_endpoints_have_moderate_rate_limiting()
    {
        Survey::factory()->create(['id' => 1, 'status' => 'published']);

        for ($i = 0; $i < 30; $i++) {
            $response = $this->getJson('/api/v1/surveys');

            if ($i < 29) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->getJson('/api/v1/surveys');
        $response->assertStatus(429);
    }

    public function test_standard_api_endpoints_have_normal_rate_limiting()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        for ($i = 0; $i < 60; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->getJson('/api/v1/auth/user');

            if ($i < 59) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/auth/user');

        $response->assertStatus(429);
    }

    public function test_admin_endpoints_have_higher_rate_limits()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        for ($i = 0; $i < 120; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->getJson('/api/v1/admin/surveys');

            if ($i < 119) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/admin/surveys');

        $response->assertStatus(429);
    }

    public function test_export_endpoints_have_strict_rate_limiting()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->postJson('/api/v1/admin/export/surveys', ['format' => 'csv']);

            if ($i < 9) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys', ['format' => 'csv']);

        $response->assertStatus(429);
    }

    public function test_import_endpoints_have_very_strict_rate_limiting()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        for ($i = 0; $i < 5; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->getJson('/api/v1/admin/import/templates?type=surveys');

            if ($i < 4) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/admin/import/templates?type=surveys');

        $response->assertStatus(429);
    }

    public function test_rate_limit_headers_are_present()
    {
        $response = $this->getJson('/api/v1/surveys');

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    public function test_rate_limit_headers_show_correct_values()
    {
        $response = $this->getJson('/api/v1/surveys');

        $limit = $response->headers->get('X-RateLimit-Limit');
        $remaining = $response->headers->get('X-RateLimit-Remaining');

        $this->assertEquals('30', $limit);
        $this->assertEquals('29', $remaining);
    }

    public function test_rate_limits_are_per_ip_address()
    {
        $response1 = $this->fromUser($this->user)
            ->getJson('/api/v1/auth/user');
        $response1->assertStatus(200);

        $response2 = $this->fromUser($this->admin)
            ->getJson('/api/v1/auth/user');
        $response2->assertStatus(200);

        $this->assertEquals(
            $response1->headers->get('X-RateLimit-Remaining'),
            $response2->headers->get('X-RateLimit-Remaining')
        );
    }

    public function test_rate_limits_reset_after_time_window()
    {
        $loginData = ['email' => 'test@example.com', 'password' => 'password'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/admin/login', $loginData);
        }

        $response = $this->postJson('/api/v1/auth/admin/login', $loginData);
        $response->assertStatus(429);

        $this->travel(61)->seconds();

        $response = $this->postJson('/api/v1/auth/admin/login', $loginData);
        $this->assertNotEquals(429, $response->status());
    }

    public function test_rate_limit_exceptions_for_different_user_types()
    {
        $adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $userToken = $this->user->createToken('test-token')->plainTextToken;

        for ($i = 0; $i < 60; $i++) {
            $this->withHeaders(['Authorization' => 'Bearer '.$userToken])
                ->getJson('/api/v1/auth/user');
        }

        $userResponse = $this->withHeaders(['Authorization' => 'Bearer '.$userToken])
            ->getJson('/api/v1/auth/user');
        $userResponse->assertStatus(429);

        $adminResponse = $this->withHeaders(['Authorization' => 'Bearer '.$adminToken])
            ->getJson('/api/v1/admin/surveys');
        $adminResponse->assertStatus(200);
    }

    public function test_rate_limiting_middleware_configuration()
    {
        $kernel = app()->make(\App\Http\Kernel::class);
        $middlewareGroups = $kernel->getMiddlewareGroups();

        $this->assertArrayHasKey('api', $middlewareGroups);
        $this->assertContains('rate_limit', $middlewareGroups['api']);
    }

    public function test_survey_response_submission_rate_limiting()
    {
        $survey = Survey::factory()->create(['status' => 'published']);
        $token = $this->lineUser->createToken('test-token')->plainTextToken;

        $responseData = [
            'survey_id' => $survey->id,
            'answers' => json_encode(['q1' => 'answer']),
        ];

        for ($i = 0; $i < 30; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->postJson('/api/v1/survey-responses', $responseData);

            if ($i < 29) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(429);
    }

    public function test_rate_limit_key_generation_for_authenticated_users()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/auth/user');

        $response->assertStatus(200);

        $key = 'user:'.$this->user->id.':standard';
        $this->assertTrue(RateLimiter::tooManyAttempts($key, 60));
    }

    public function test_broadcasting_auth_has_separate_rate_limiting()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        for ($i = 0; $i < 60; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->postJson('/api/v1/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-surveys',
            ]);

            if ($i < 59) {
                $this->assertNotEquals(429, $response->status());
            }
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-surveys',
        ]);

        $response->assertStatus(429);
    }

    protected function fromUser($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;

        return $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ]);
    }
}
