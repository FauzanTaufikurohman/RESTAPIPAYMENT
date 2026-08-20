# REST API Wallet & Auth

A Laravel-based REST API for user authentication, wallet management, transactions, and background money transfer processing.

## Overview

This project provides APIs for:

- User registration
- Login using phone number + PIN
- JWT-based authentication
- Top-up balance
- Payment deduction
- Money transfer between users
- Transaction history report
- Profile update

The application is designed to be easy to understand, run, and extend for learning or production-like testing.

## Tech Stack

- PHP 8.3
- Laravel 13
- MySQL / SQLite for local development
- Firebase JWT
- Laravel Queue system for async transfer processing

## Project Structure

- `app/Http/Controllers/Api` - API controllers
- `app/Models` - Eloquent models
- `app/Jobs` - background jobs
- `app/Support` - helper classes such as JWT generator
- `database/migrations` - database schema
- `routes/api.php` - API routes
- `tests/Feature` - feature test coverage

## Features

### 1. Register
Creates a new user with UUID-based user_id and unique phone number.

Request body:

```json
{
  "first_name": "Alice",
  "last_name": "Sample",
  "address": "Bandung",
  "email": "alice@example.com",
  "phone_number": "081234567890",
  "pin": "123456",
  "password": "secret123"
}
```

### 2. Login
Uses phone number and PIN to authenticate.

Request body:

```json
{
  "phone_number": "081234567890",
  "pin": "123456"
}
```

Response contains a JWT token.

### 3. Top Up
Adds balance to the authenticated user.

### 4. Payment
Deducts balance for a purchase or payment.

### 5. Transfer
Transfers money to another user. The request is queued and processed asynchronously.

### 6. Report Transactions
Returns the user's transaction history and transfer records.

### 7. Update Profile
Updates profile data excluding unique identifiers.

## Authentication

This API uses JWT tokens.

Protected endpoints require a token in the Authorization header. The app accepts the following formats:

```http
Authorization: <jwt_token>
```

or

```http
Authorization: Bearer <jwt_token>
```

## API Endpoints

### Public

#### Register
```http
POST /api/register
```

#### Login
```http
POST /api/login
```

### Protected

#### Top Up
```http
POST /api/top-ups
```

#### Payment
```http
POST /api/payments
```

#### Transfer
```http
POST /api/transfers
```

#### Transaction Report
```http
GET /api/transactions
```

#### Profile
```http
GET /api/profile
```

#### Update Profile
```http
PUT /api/profile
```

## Example Requests

### Login
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "081234567890",
    "pin": "123456"
  }'
```

### Top Up
```bash
curl -X POST http://127.0.0.1:8000/api/top-ups \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <jwt_token>" \
  -d '{
    "amount": 50000
  }'
```

### Transfer
```bash
curl -X POST http://127.0.0.1:8000/api/transfers \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <jwt_token>" \
  -d '{
    "receiver_phone": "081234567891",
    "amount": 10000,
    "description": "Dinner"
  }'
```

## Setup Instructions

### 1. Clone the repository
```bash
git clone <repository-url>
cd restapi
```

### 2. Install dependencies
```bash
composer install
```

### 3. Configure environment file
Copy the example environment file:

```bash
cp .env.example .env
```

Then set the application key:

```bash
php artisan key:generate
```

Make sure the JWT secret is configured in `.env`:

```env
JWT_SECRET=restapi-jwt-secret-key-2026-very-long
```

### 4. Configure database
Update `.env` to your database settings. For example, using SQLite local setup:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite
```

Or MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payment
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run migrations
```bash
php artisan migrate
```

### 6. Run the app
```bash
php artisan serve
```

The app will be available at:

```text
http://127.0.0.1:8000
```

## Queue / Background Transfer

Transfer processing runs asynchronously using Laravel queues.

Start the queue worker:

```bash
php artisan queue:work
```

This allows transfers to be queued immediately and processed in the background.

## Testing

Run the API test suite:

```bash
php artisan test
```

Or run only wallet/auth tests:

```bash
php artisan test tests/Feature/AuthAndWalletApiTest.php
```

## Notes

- This project is intended for backend API learning and demonstration.
- For production use, consider adding:
  - validation and rate limiting
  - stronger password and PIN rules
  - transaction audit logging
  - admin dashboard for queue monitoring
  - HTTPS and environment-specific secrets

## License

This project is open for learning and personal use. You may adapt it for your own projects.

## Contact

Use your GitHub repository details or maintainer profile here if you want to share contact information.
