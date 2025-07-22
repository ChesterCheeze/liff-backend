#!/bin/bash

# Integration Test Runner for Laravel Backend App
# This script runs the integration test suite with proper setup

echo "🚀 Laravel Backend Integration Test Suite"
echo "=========================================="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

echo "📋 Preparing test environment..."

# Clear any existing caches
php artisan config:clear
php artisan cache:clear

# Run database migrations for testing
echo "🗄️  Setting up test database..."
php artisan migrate:fresh --env=testing --quiet

echo "🧪 Running Integration Tests..."
echo ""

# Run specific integration test categories
echo "1️⃣  Testing API Workflows..."
php artisan test tests/Feature/Integration/ApiWorkflowTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ API Workflow tests passed"
else
    echo "❌ API Workflow tests failed"
    exit 1
fi

echo ""
echo "2️⃣  Testing Database Integration..."
php artisan test tests/Feature/Integration/DatabaseIntegrationTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ Database Integration tests passed"
else
    echo "❌ Database Integration tests failed"
    exit 1
fi

echo ""
echo "3️⃣  Testing Security Features..."
php artisan test tests/Feature/Integration/SecurityIntegrationTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ Security Integration tests passed"
else
    echo "❌ Security Integration tests failed"
    exit 1
fi

echo ""
echo "4️⃣  Testing Analytics Integration..."
php artisan test tests/Feature/Integration/AnalyticsIntegrationTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ Analytics Integration tests passed"
else
    echo "❌ Analytics Integration tests failed"
    exit 1
fi

echo ""
echo "5️⃣  Testing Export/Import Features..."
php artisan test tests/Feature/Integration/ExportImportIntegrationTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ Export/Import Integration tests passed"
else
    echo "❌ Export/Import Integration tests failed"
    exit 1
fi

echo ""
echo "6️⃣  Testing Database Seeding..."
php artisan test tests/Feature/Integration/SeedingIntegrationTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ Seeding Integration tests passed"
else
    echo "❌ Seeding Integration tests failed"
    exit 1
fi

echo ""
echo "7️⃣  Testing Performance..."
php artisan test tests/Feature/Integration/PerformanceIntegrationTest.php --stop-on-failure

if [ $? -eq 0 ]; then
    echo "✅ Performance Integration tests passed"
else
    echo "❌ Performance Integration tests failed"
    exit 1
fi

echo ""
echo "🎉 All Integration Tests Completed Successfully!"
echo "==============================================="
echo ""
echo "📊 Summary:"
echo "  ✅ API Workflows"
echo "  ✅ Database Integration" 
echo "  ✅ Security Features"
echo "  ✅ Analytics Integration"
echo "  ✅ Export/Import Features"
echo "  ✅ Database Seeding"
echo "  ✅ Performance Testing"
echo ""
echo "🏆 Integration test suite passed! Your application is ready for deployment."