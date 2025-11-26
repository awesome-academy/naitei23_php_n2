-- DROP & CREATE DATABASE (optional, dùng khi setup mới)
DROP DATABASE IF EXISTS workspace_booking;
CREATE DATABASE workspace_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE workspace_booking;

-- =========================
-- ROLES & USERS
-- =========================

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE COMMENT 'admin, moderator, venue_owner, customer',
    description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NULL,
    profile_avatar_url VARCHAR(255) NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,

    verification_token VARCHAR(100) NULL,
    password_reset_token VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (user_id, role_id),

    CONSTRAINT fk_user_roles_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- SPACE TYPES / VENUES / SPACES
-- =========================

CREATE TABLE space_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Private Office, Meeting Room, Desk'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    street VARCHAR(255) NULL,

    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,

    status ENUM('pending', 'approved', 'blocked') NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_venues_owner
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_venues_owner (owner_id),
    INDEX idx_venues_city (city),
    INDEX idx_venues_status (status),
    INDEX idx_venues_city_status (city, status) -- Composite index cho filter kết hợp
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venue_managers (
    venue_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (venue_id, user_id),

    CONSTRAINT fk_venue_managers_venue
        FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    CONSTRAINT fk_venue_managers_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE spaces (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venue_id BIGINT UNSIGNED NOT NULL,
    space_type_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,

    price_per_hour DECIMAL(10,2) NULL,
    price_per_day DECIMAL(10,2) NULL,
    price_per_month DECIMAL(10,2) NULL,

    open_hour TIME NULL DEFAULT '08:00:00',
    close_hour TIME NULL DEFAULT '18:00:00',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_spaces_venue
        FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    CONSTRAINT fk_spaces_type
        FOREIGN KEY (space_type_id) REFERENCES space_types(id),

    INDEX idx_spaces_venue (venue_id),
    INDEX idx_spaces_type (space_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Space managers (nếu sau dùng quản lý theo từng phòng)
CREATE TABLE space_managers (
    space_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (space_id, user_id),

    CONSTRAINT fk_space_managers_space
        FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE CASCADE,
    CONSTRAINT fk_space_managers_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- AMENITIES & SERVICES
-- =========================

CREATE TABLE amenities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amenity_name VARCHAR(100) NOT NULL UNIQUE,
    icon_url VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venue_amenities (
    venue_id BIGINT UNSIGNED NOT NULL,
    amenity_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (venue_id, amenity_id),

    CONSTRAINT fk_venue_amenities_venue
        FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    CONSTRAINT fk_venue_amenities_amenity
        FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venue_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_services_venue
        FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,

    INDEX idx_services_venue (venue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- BOOKINGS & PAYMENTS
-- =========================

CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    space_id BIGINT UNSIGNED NULL,

    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,

    total_price DECIMAL(10,2) NOT NULL,

    status ENUM(
        'pending_confirmation', -- Chờ chủ/mod duyệt
        'awaiting_payment',     -- Đã duyệt, chờ thanh toán
        'confirmed',            -- Đã thanh toán
        'cancelled',            -- Đã huỷ
        'completed'             -- Đã hoàn thành (hết thời gian)
    ) NOT NULL DEFAULT 'pending_confirmation',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_space
        FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE SET NULL,

    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_space (space_id),
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_time (space_id, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_services (
    booking_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_booking DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (booking_id, service_id),

    CONSTRAINT fk_booking_services_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_services_service
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,

    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NULL COMMENT 'VNPAY, MoMo, Stripe, Bank Transfer',
    transaction_id VARCHAR(255) NULL COMMENT 'Mã giao dịch từ cổng thanh toán',

    transaction_status ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,

    INDEX idx_payments_status (transaction_status) -- Filter payments theo trạng thái
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- NOTIFICATIONS
-- =========================

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,

    type ENUM('email', 'sms', 'app') NOT NULL DEFAULT 'app',

    is_read TINYINT(1) NOT NULL DEFAULT 0,
    related_url VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- CHAT (CONVERSATIONS & MESSAGES)
-- =========================

CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conversation_participants (
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (conversation_id, user_id),

    CONSTRAINT fk_conv_part_conversation
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_conv_part_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message_body TEXT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_messages_conversation
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_messages_conv (conversation_id),
    INDEX idx_messages_sender (sender_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
