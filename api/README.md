# Travia Tour REST API

This directory contains the REST API endpoints for the Travia Tour application.

## User Verification API

### Endpoint: `/api/verify_user.php`

This API endpoint verifies if a user exists and is verified in the system.

#### Request Method
- POST

#### Request Parameters
- `email` - The user's email address
- `password_hash` - The hashed password
- `token` - The API authentication token

#### Response Format (JSON)

**Successful response** (user exists and is verified):
```json
{
    "connexion": true,
    "user_id": "123",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "verified_account": true,
    "creation_datetime": "2023-01-01 12:00:00",
    "token": "generated_token_for_third_party_apps"
}
```

**Failed response** (user doesn't exist or isn't verified):
```json
{
    "connexion": false,
    "error": "Error message"
}
```

## Database Requirements

The API requires the following columns in the `users` table:
- `id` - User ID
- `first_name` - User's first name
- `last_name` - User's last name
- `email` - User email
- `password` - Hashed password
- `home_planet` - User's home planet
- `work_planet` - User's work planet
- `is_verified` - Boolean flag indicating if the account is verified
- `verification_token` - Token for email verification
- `verification_expiry` - Expiration timestamp for verification token
- `login_code` - Code for login authentication
- `login_code_expiry` - Expiration timestamp for login code
- `created_at` - Account creation timestamp
- `last_login` - Timestamp of last login
- `token_expiry` - Expiration timestamp for general tokens
- `api_token` - Token for third-party applications
- `api_token_expiry` - Expiration timestamp for the API token

You can run the `db_update.sql` script to add the required API token columns to your existing users table.

## Testing the API

You can test the API using the included test client:

1. Navigate to `/api/test_client.php` in your browser
2. Enter a valid email and password
3. The API token is pre-filled from the configuration
4. Click "Test API" to see the response

## Debug Tools

The API includes several debug tools to help diagnose issues:

- **API Status Test** (`/api/test.php`): Checks if the API is running correctly and returns basic information
- **System Information** (`/api/debug.php`): Provides detailed information about the server environment, PHP configuration, and database connection

## API Security

The API uses a token-based authentication system to ensure that only authorized applications can access the endpoints. The token is defined in the `config/api_config.php` file.

In a production environment, consider:
- Using HTTPS for all API requests
- Implementing rate limiting
- Restricting CORS to specific domains
- Using environment variables for sensitive information like API tokens

## Error Codes

The API may return the following error messages:

- "Missing required fields" - One or more required parameters are missing
- "Invalid API token" - The provided API token is incorrect
- "Account not verified" - The user account exists but has not been verified
- "Invalid credentials" - The email/password combination is incorrect
- "User does not exist" - No user with the provided email exists
- "Database error" - An error occurred while accessing the database

## Troubleshooting

If you encounter issues with the API, try the following:

1. Check the API status using `/api/test.php`
2. View system information using `/api/debug.php`
3. Ensure the database connection is properly configured
4. Verify that the API token in your request matches the one in `config/api_config.php`
5. Check that the user exists and is verified in the database
6. Ensure the `api_token` and `api_token_expiry` columns exist in the users table 