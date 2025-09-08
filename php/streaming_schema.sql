-- Database schema for streaming content management system

-- Table for user-created lists
CREATE TABLE IF NOT EXISTS user_lists (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    list_name VARCHAR(255) NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_lists_user_id (user_id),
    INDEX idx_user_lists_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for streaming content items
CREATE TABLE IF NOT EXISTS streaming_content (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    youtube_video_id VARCHAR(50) NOT NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    thumbnail_url VARCHAR(500),
    duration VARCHAR(20),
    channel_title VARCHAR(255),
    published_at DATETIME,
    added_by_user_id INT UNSIGNED NOT NULL,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (added_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_youtube_video (youtube_video_id),
    INDEX idx_streaming_content_user (added_by_user_id),
    INDEX idx_streaming_content_added_at (added_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Junction table for list items (many-to-many relationship)
CREATE TABLE IF NOT EXISTS list_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    list_id INT UNSIGNED NOT NULL,
    content_id INT UNSIGNED NOT NULL,
    position_order INT UNSIGNED DEFAULT 0,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (list_id) REFERENCES user_lists(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES streaming_content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_list_content (list_id, content_id),
    INDEX idx_list_items_list (list_id),
    INDEX idx_list_items_content (content_id),
    INDEX idx_list_items_position (position_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for user follows (users can follow other users' lists)
CREATE TABLE IF NOT EXISTS user_follows (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    follower_user_id INT UNSIGNED NOT NULL,
    followed_user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (follower_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_user_id, followed_user_id),
    INDEX idx_follows_follower (follower_user_id),
    INDEX idx_follows_followed (followed_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for YouTube API credentials (for OAuth)
CREATE TABLE IF NOT EXISTS youtube_credentials (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_youtube (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
