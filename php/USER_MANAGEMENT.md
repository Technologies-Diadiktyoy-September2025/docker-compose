# User Management System

This document describes the user management functionality implemented in the My Site application.

## Features

### Authentication

- **User Registration** (`register.php`) - Create new user accounts
- **User Login** (`login.php`) - Authenticate existing users
- **User Logout** (`logout.php`) - End user sessions
- **Session Management** - Secure session handling with PHP sessions

### Profile Management

- **Profile Display** (`profile.php`) - View user account information
- **Profile Editing** (`edit_profile.php`) - Update personal information and password
- **Account Deletion** (`delete_profile.php`) - Permanently remove user accounts

### Admin Features

- **User Management** (`admin.php`) - View all registered users
- **Database Overview** - Monitor user statistics

## Database Schema

The system uses a single `users` table with the following structure:

```sql
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  username VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
);
```

## Security Features

### Password Security

- Passwords are hashed using PHP's `password_hash()` function with `PASSWORD_DEFAULT`
- Password verification uses `password_verify()` for secure comparison
- Minimum password length of 6 characters enforced

### Session Security

- PHP sessions are used for user authentication
- Session data includes user ID, username, and profile information
- Automatic session destruction on logout and account deletion

### Input Validation

- All user inputs are validated and sanitized
- Email format validation using `filter_var()`
- SQL injection prevention using prepared statements
- XSS prevention using `htmlspecialchars()`

### Account Deletion Security

- Multi-step confirmation process for account deletion
- Requires current password verification
- Requires typing "DELETE" confirmation
- Requires username confirmation
- Transaction-based deletion to ensure data integrity

## File Structure

```
├── auth.php              # Authentication helper functions
├── db.php                # Database connection configuration
├── login.php             # User login page
├── logout.php            # User logout handler
├── profile.php           # User profile display
├── edit_profile.php      # Profile editing form
├── delete_profile.php    # Account deletion page
├── admin.php             # Admin user management panel
├── register.php          # User registration (existing)
└── users.sql             # Database schema
```

## Usage

### For Users

1. **Register**: Visit `register.php` to create a new account
2. **Login**: Use `login.php` to access your account
3. **View Profile**: Access `profile.php` to see your information
4. **Edit Profile**: Use `edit_profile.php` to update your details
5. **Delete Account**: Use `delete_profile.php` to permanently remove your account

### For Administrators

1. **Login**: Use any user account to access admin features
2. **View Users**: Access `admin.php` to see all registered users
3. **Monitor**: Check user statistics and database information

## Navigation

The navigation system has been updated to include:

- **Login/Logout links** on all pages
- **Profile access** for authenticated users
- **Admin panel** access from the profile page

## Error Handling

The system includes comprehensive error handling for:

- Database connection failures
- Invalid user credentials
- Duplicate username/email registration
- Password validation errors
- Session management issues

## Future Enhancements

Potential improvements for the user management system:

- Role-based access control (admin, user, moderator)
- Password reset functionality
- Email verification for new accounts
- User activity logging
- Advanced admin features (user suspension, bulk operations)
- Integration with user lists/collections system

