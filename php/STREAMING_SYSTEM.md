# Streaming Content Management System

A comprehensive PHP-based system for managing YouTube streaming content lists with full OAuth integration and playlist functionality.

## 🎯 Features

### Core Functionality

- **List Management**: Create, edit, and manage streaming content lists
- **YouTube Integration**: Search and add YouTube videos using the YouTube Data API v3
- **OAuth Authentication**: Secure YouTube API access with OAuth 2.0
- **Video Playback**: Embedded YouTube player with playlist functionality
- **User Following**: Follow other users and view their public lists
- **Privacy Controls**: Public/private list visibility options

### Database Schema

- **user_lists**: User-created streaming content lists
- **streaming_content**: YouTube video metadata and information
- **list_items**: Many-to-many relationship between lists and content
- **user_follows**: User following relationships
- **youtube_credentials**: OAuth tokens for YouTube API access

## 🚀 Quick Start

### 1. Database Setup

```bash
# Run the setup script to create all necessary tables
http://localhost/rigas-ergasia/setup_streaming.php
```

### 2. YouTube API Configuration

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable YouTube Data API v3
4. Create OAuth 2.0 credentials
5. Set authorized redirect URIs to include your domain
6. Update credentials in `youtube_config.php`

### 3. Configuration

Update `youtube_config.php` with your API credentials:

```php
define('YOUTUBE_CLIENT_ID', 'your_client_id_here.apps.googleusercontent.com');
define('YOUTUBE_CLIENT_SECRET', 'your_client_secret_here');
define('YOUTUBE_REDIRECT_URI', 'http://localhost/rigas-ergasia/youtube_oauth_callback.php');
define('YOUTUBE_API_KEY', 'your_api_key_here');
```

## 📁 File Structure

### Core Pages

- **`my_lists.php`** - Main lists management page
- **`search_content.php`** - YouTube video search and addition
- **`view_list.php`** - Display list contents
- **`play_video.php`** - Video playback with playlist support
- **`play_list.php`** - Start playlist playback
- **`edit_list.php`** - Edit list properties
- **`add_content.php`** - Redirect to search with list context

### API Integration

- **`youtube_config.php`** - YouTube API configuration and functions
- **`youtube_oauth_callback.php`** - OAuth callback handler

### Database

- **`streaming_schema.sql`** - Complete database schema
- **`setup_streaming.php`** - Database setup script

### Utilities

- **`remove_from_list.php`** - Remove videos from lists
- **`auth.php`** - Authentication helper functions

## 🔧 API Functions

### YouTube API Integration

```php
// Search for videos
$results = searchYouTubeVideos($query, $accessToken, $maxResults);

// Get video details
$details = getYouTubeVideoDetails($videoId, $accessToken);

// OAuth URL generation
$authUrl = getYouTubeOAuthUrl();

// Token management
$tokenData = exchangeCodeForToken($code);
$newToken = refreshAccessToken($refreshToken);
```

### Authentication Helpers

```php
// Check if user is logged in
if (isLoggedIn()) { ... }

// Require login (redirect if not)
requireLogin();

// Get current user data
$user = getCurrentUser();
```

## 🎬 User Workflow

### 1. First Time Setup

1. **Register/Login** to the system
2. **Connect YouTube Account** via OAuth
3. **Create First List** with name and description
4. **Search for Videos** using YouTube API
5. **Add Videos** to your lists

### 2. Content Management

1. **Create Lists** for different topics/themes
2. **Search YouTube** for relevant content
3. **Add Videos** with full metadata
4. **Organize Content** with drag-and-drop ordering
5. **Share Lists** by making them public

### 3. Playback Experience

1. **View List** to see all videos
2. **Play Individual Videos** with embedded player
3. **Play All** to start playlist mode
4. **Navigate Playlist** with previous/next controls
5. **Follow Users** to discover new content

## 🔒 Security Features

### Authentication

- **Session-based authentication** for user management
- **OAuth 2.0** for YouTube API access
- **Token refresh** for long-term API access
- **Permission checks** for list access and editing

### Data Protection

- **SQL injection prevention** with prepared statements
- **XSS protection** with htmlspecialchars()
- **Input validation** for all user inputs
- **CSRF protection** through session validation

### Privacy Controls

- **Public/Private lists** with visibility controls
- **User following system** for content discovery
- **Owner-only editing** for list management
- **Secure credential storage** for API tokens

## 📊 Database Schema Details

### user_lists Table

```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- list_name (VARCHAR 255)
- description (TEXT)
- is_public (BOOLEAN)
- created_at, updated_at (TIMESTAMPS)
```

### streaming_content Table

```sql
- id (Primary Key)
- youtube_video_id (VARCHAR 50, UNIQUE)
- title (VARCHAR 500)
- description (TEXT)
- thumbnail_url (VARCHAR 500)
- duration (VARCHAR 20)
- channel_title (VARCHAR 255)
- published_at (DATETIME)
- added_by_user_id (Foreign Key)
- added_at (TIMESTAMP)
```

### list_items Table (Junction)

```sql
- id (Primary Key)
- list_id (Foreign Key to user_lists)
- content_id (Foreign Key to streaming_content)
- position_order (INT UNSIGNED)
- added_at (TIMESTAMP)
```

## 🎨 UI Features

### Responsive Design

- **Mobile-friendly** interface
- **Theme switching** (light/dark modes)
- **Accordion sections** for organized content
- **Card-based layout** for clean presentation

### Interactive Elements

- **Embedded YouTube player** with full controls
- **Playlist navigation** with previous/next
- **Drag-and-drop** list organization
- **Real-time search** with YouTube API
- **One-click video addition** to lists

## 🔧 Configuration Options

### YouTube API Settings

- **Search parameters** (max results, order, type)
- **OAuth scopes** for API access
- **Token expiration** handling
- **Rate limiting** considerations

### System Settings

- **Database connection** parameters
- **Session configuration** options
- **File upload** limits
- **Cache settings** for performance

## 🚀 Deployment

### Requirements

- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or MariaDB 10.3+
- **Apache/Nginx** web server
- **SSL certificate** for OAuth (recommended)

### Production Setup

1. **Configure database** with production credentials
2. **Set up SSL** for secure OAuth callbacks
3. **Update API credentials** with production URLs
4. **Configure caching** for better performance
5. **Set up monitoring** for API usage

## 📈 Future Enhancements

### Planned Features

- **Advanced search filters** (date, duration, channel)
- **List sharing** with direct links
- **Collaborative lists** with multiple editors
- **Content recommendations** based on viewing history
- **Export functionality** for list backup
- **Mobile app** integration
- **Social features** (likes, comments, ratings)

### Technical Improvements

- **Caching layer** for API responses
- **Background job processing** for bulk operations
- **Real-time updates** with WebSockets
- **Advanced analytics** for usage tracking
- **API rate limiting** and optimization

## 🐛 Troubleshooting

### Common Issues

1. **YouTube API quota exceeded** - Check API usage in Google Cloud Console
2. **OAuth redirect mismatch** - Verify redirect URI in API credentials
3. **Database connection failed** - Check MySQL service and credentials
4. **Token expired** - System automatically refreshes tokens
5. **Search not working** - Verify YouTube API key and OAuth setup

### Debug Tools

- **`debug_db.php`** - Database connection testing
- **`test_db.php`** - Database functionality verification
- **`phpinfo.php`** - PHP configuration display
- **Browser developer tools** - Network and console debugging

## 📝 License

This streaming content management system is part of the My Site project and follows the same licensing terms.

---

**Note**: This system requires proper YouTube API credentials and OAuth setup to function. Make sure to configure the YouTube Data API v3 in Google Cloud Console before use.
