# Testing Walkthrough

This document guides you through the process of running tests for the Laravel backend.

## Prerequisites

Ensure you have installed the backend dependencies:

```bash
cd backend
composer install
```

## Running PHP Tests

The project uses Laravel's built-in testing tools (based on PHPUnit/Pest).

To run all tests, execute the following command in the `backend` directory:

```bash
php artisan test
```

### Understanding the Output

- **PASS**: The test case passed successfully.
- **FAIL**: The test case failed. The output will show the expected value vs. the actual value.
- **ERROR**: An error occurred during the test execution (e.g., database connection issue).

## Test Structure

- **Feature Tests** (`tests/Feature`): Test larger chunks of code, such as HTTP requests to API endpoints.
- **Unit Tests** (`tests/Unit`): Test individual methods and classes in isolation.

## Troubleshooting

If tests fail due to database issues, ensure your `.env.testing` file (if it exists) or your `.env` is correctly configured for testing. Laravel usually handles an in-memory SQLite database for testing if configured in `phpunit.xml`.
