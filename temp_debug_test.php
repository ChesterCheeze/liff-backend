<?php

require_once 'vendor/autoload.php';

$testCase = new Tests\TestCase();
$testCase->setUp();

$response = $testCase->postJson('/api/v1/auth/register', [
    'name' => 'Test User',
    'email' => 'testuser@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
]);

echo 'Status: '.$response->getStatusCode()."\n";
echo 'Content: '.$response->getContent()."\n";
