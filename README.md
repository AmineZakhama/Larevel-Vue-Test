# Laravel + Vue Test Project

This project consists of a Laravel backend and a Vue 3 frontend.

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js >= 20
- MySQL or SQLite (configured in `.env`)

## Installation & Setup

### Backend (Laravel)

1.  Navigate to the backend directory:

    ```bash
    cd backend
    ```

2.  Install PHP dependencies:

    ```bash
    composer install
    ```

3.  Configure environment variables:

    ```bash
    cp .env.example .env
    ```

    Edit `.env` and set your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4.  Generate application key:

    ```bash
    php artisan key:generate
    ```

5.  Run migrations:

    ```bash
    php artisan migrate
    ```

6.  Start the backend server:
    ```bash
    php artisan serve
    ```
    The API will be available at `http://localhost:8000`.

### Frontend (Vue 3)

1.  Navigate to the frontend directory:

    ```bash
    cd frontend
    ```

2.  Install Node dependencies:

    ```bash
    npm install
    ```

3.  Start the development server:
    ```bash
    npm run dev
    ```
    The application will be available at `http://localhost:5173`.

## Database Schema

See [database_schema.md](database_schema.md) for details on the database structure.

## Testing

See [testing_walkthrough.md](testing_walkthrough.md) for a guide on running tests.
