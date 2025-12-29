# URL Shortening API

A Laravel-based URL shortening service with authentication and comprehensive error handling. This API provides endpoints for user registration, authentication, and URL shortening with unique short codes.

## Features

-   User registration and authentication using Laravel Sanctum
-   Store original URLs with unique 6-character short codes
-   Public redirect endpoint to expand short URLs to original URLs
-   Duplicate URL detection (returns existing short code if user already shortened the URL)
-   Comprehensive error handling for validation, authentication, and authorization
-   RESTful API design with consistent response formatting

## Database Schema

The `shortened_urls` table contains:

-   `id` - Primary key (integer)
-   `user_id` - Foreign key to users table (integer)
-   `original_url` - The original long URL (string, max 2048 characters)
-   `short_code` - Unique short code (string, 6 characters)
-   `created_at` - Timestamp when URL was shortened (datetime)
-   `updated_at` - Timestamp when URL was last updated (datetime)

## API Endpoints Overview

### Authentication Endpoints

| Method | Endpoint             | Authentication | Description                    |
| ------ | -------------------- | -------------- | ------------------------------ |
| POST   | `/api/auth/register` | None           | Register a new user            |
| POST   | `/api/auth/login`    | None           | Login and receive access token |
| POST   | `/api/auth/logout`   | Bearer Token   | Logout and revoke access token |

### URL Management Endpoints

| Method | Endpoint                | Authentication | Description                              |
| ------ | ----------------------- | -------------- | ---------------------------------------- |
| POST   | `/api/auth/shorten-url` | Bearer Token   | Create a new shortened URL               |
| GET    | `/api/s/{shortCode}`    | None           | Redirect from short code to original URL |

---

## Detailed Endpoint Documentation

### 1. Register a New User

**Endpoint:** `POST /api/auth/register`

**Authentication:** Not required

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123"
}
```

**Request Parameters:**

-   `name` (string, required): User's full name (max 255 characters)
-   `email` (string, required): User's email address (must be unique, valid email format)
-   `password` (string, required): User's password (min 8 characters, must include uppercase, number, and special character)

**Success Response (201 Created):**

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-12-30T10:30:00.000000Z"
        },
        "access_token": "1|abcdefghijklmnopqrstuvwxyz123456789",
        "token_type": "Bearer"
    }
}
```

**Error Responses:**

Validation Error (422):

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123"
  }'
```

---

### 2. User Login

**Endpoint:** `POST /api/auth/login`

**Authentication:** Not required

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "SecurePassword123"
}
```

**Request Parameters:**

-   `email` (string, required): Registered user's email
-   `password` (string, required): User's password

**Success Response (200 OK):**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-12-30T10:30:00.000000Z"
        },
        "access_token": "2|abcdefghijklmnopqrstuvwxyz123456789",
        "token_type": "Bearer"
    }
}
```

**Error Response - Invalid Credentials (401):**

```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

**Validation Error (422):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword123"
  }'
```

---

### 3. Create Shortened URL

**Endpoint:** `POST /api/auth/shorten-url`

**Authentication:** Required (Bearer Token)

**Request Body:**

```json
{
    "original_url": "https://www.example.com/very/long/path?param=value&another=param"
}
```

**Request Parameters:**

-   `original_url` (string, required): Valid URL to be shortened (must be a valid URL, max 2048 characters)

**Headers:**

```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
```

**Success Response (201 Created):**

```json
{
    "success": true,
    "message": "URL shortened successfully",
    "data": {
        "short_code": "AbC123",
        "original_url": "https://www.example.com/very/long/path?param=value&another=param",
        "shortened_url": "http://localhost:8000/s/AbC123",
        "created_at": "2025-12-30T10:35:00.000000Z"
    }
}
```

**Duplicate URL Response (200 OK):**

```json
{
    "success": true,
    "message": "URL already shortened",
    "data": {
        "short_code": "AbC123",
        "original_url": "https://www.example.com/very/long/path?param=value&another=param",
        "shortened_url": "http://localhost:8000/s/AbC123",
        "created_at": "2025-12-30T10:30:00.000000Z"
    }
}
```

**Error Responses:**

Validation Error (422):

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "original_url": [
            "The original url field is required.",
            "The original url must be a valid URL."
        ]
    }
}
```

Unauthorized (401):

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/auth/shorten-url \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "original_url": "https://www.example.com/very/long/path"
  }'
```

---

### 4. Redirect to Original URL

**Endpoint:** `GET /api/s/{shortCode}`

**Authentication:** Not required

**Path Parameters:**

-   `shortCode` (string, required): The 6-character short code

**Success Response (302 Found):**
Redirects to the original URL with HTTP 302 status code.

**Error Response - Invalid Short Code (404):**

```json
{
    "success": false,
    "message": "Invalid short code",
    "data": {
        "short_code": "invalid"
    }
}
```

**cURL Example:**

```bash
# Will redirect to the original URL
curl -L http://localhost:8000/api/s/AbC123
```

**Browser Example:**
Simply visit `http://localhost:8000/s/AbC123` in your browser to be redirected.

---

### 5. User Logout

**Endpoint:** `POST /api/auth/logout`

**Authentication:** Required (Bearer Token)

**Request Body:** Empty (no body required)

**Headers:**

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

**Success Response (200 OK):**

```json
{
    "success": true,
    "message": "Logged out successfully 2",
    "data": null
}
```

**Unauthorized (401):**

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## Response Format

### Success Response Structure

All successful responses follow this format:

```json
{
    "success": true,
    "message": "Action completed successfully",
    "data": {
        "key": "value"
    }
}
```

**Fields:**

-   `success` (boolean): Always `true` for successful responses
-   `message` (string): Human-readable message describing the result
-   `data` (object|array|null): Response payload (structure varies by endpoint)

### Error Response Structure

All error responses follow this format:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Error message for this field"]
    }
}
```

**Fields:**

-   `success` (boolean): Always `false` for error responses
-   `message` (string): General error message
-   `errors` (object, optional): Field-specific validation errors

## HTTP Status Codes

| Code | Meaning               | Scenario                                                     |
| ---- | --------------------- | ------------------------------------------------------------ |
| 200  | OK                    | Successful GET/POST request returning data                   |
| 201  | Created               | Resource successfully created (registration, URL shortening) |
| 302  | Found                 | Redirect to original URL from short code                     |
| 401  | Unauthorized          | Missing or invalid authentication token                      |
| 404  | Not Found             | Resource not found (invalid short code)                      |
| 422  | Unprocessable Entity  | Validation failed                                            |
| 500  | Internal Server Error | Server-side error occurred                                   |

## Error Handling

The API handles the following scenarios:

1. **Invalid Token/Authentication Missing**: Returns 401 Unauthorized

    - Missing `Authorization` header
    - Invalid or expired token
    - Token revoked after logout

2. **Duplicate URL**: Returns 200 OK with existing short code

    - If a user tries to shorten the same URL twice, the API returns the previously created short code instead of creating a duplicate

3. **Invalid Short Code**: Returns 404 Not Found

    - When attempting to redirect using a non-existent short code

4. **Validation Errors**: Returns 422 Unprocessable Entity

    - Missing required fields
    - Invalid URL format
    - Duplicate email during registration
    - Password not meeting security requirements

5. **Server Errors**: Returns 500 Internal Server Error
    - Unexpected exceptions during processing

## Complete API Workflow Example

Here's a complete workflow to use the API:

```bash
# 1. Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "SecurePassword456"
  }'

# Store the access_token from the response

# 2. Shorten a URL (replace YOUR_ACCESS_TOKEN with the token from step 1)
curl -X POST http://localhost:8000/api/auth/shorten-url \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "original_url": "https://github.com/laravel/laravel/blob/master/readme.md"
  }'

# Store the short_code from the response

# 3. Test the redirect (replace abc123 with the actual short_code)
curl -L http://localhost:8000/api/s/abc123

# 4. Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## Setup Instructions

1. **Install Dependencies:**

```bash
composer install
npm install
npm run dev
```

2. **Configure Environment:**

```bash
cp .env.example .env
php artisan key:generate
```

3. **Setup Database:**

```bash
php artisan migrate
```

4. **Start the Development Server:**

```bash
php artisan serve
```

5. The API will be available at `http://localhost:8000/api`

## Key Features Implemented

-   **User Authentication**: Secure registration and login using Laravel Sanctum
-   **Duplicate URL Handling**: If a user tries to shorten the same URL twice, the API returns the existing short code instead of creating a duplicate
-   **Unique Short Codes**: Generates cryptographically random 6-character codes using Laravel's `Str::random()`
-   **Bearer Token Authentication**: All protected endpoints require valid Sanctum access tokens
-   **Public Redirect**: Short URL redirects work without authentication, allowing anyone with the short code to access the original URL
-   **Comprehensive Validation**:
    -   URL format validation (must be valid URL)
    -   Email uniqueness validation during registration
    -   Password security requirements (min 8 characters, uppercase, number, special character)
-   **Consistent Error Responses**: All endpoints return a standardized error format using the ApiResponse trait
-   **HTTP Status Codes**: Proper use of HTTP status codes (200, 201, 302, 401, 404, 422, 500)
