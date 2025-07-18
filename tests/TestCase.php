<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a session to enable CSRF protection
        $this->withSession(['_token' => 'test-token']);

        // Create csrf token session
        session(['_token' => 'test-token']);

        // Enable debug mode and custom error handler for tests
        config(['app.debug' => true]);

        app()->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );
    }
}
