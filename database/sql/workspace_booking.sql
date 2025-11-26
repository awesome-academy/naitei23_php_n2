CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên vai trò (admin, moderator, user)'
);

CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NULL,
    profile_avatar_url VARCHAR(255) NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    
    verification_token VARCHAR(100) NULL,
    password_reset_token VARCHAR(100) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE space_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Tên loại: Private Office, Meeting Room, Desk'
);

CREATE TABLE venues (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    street VARCHAR(255) NULL,
    
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    
    status ENUM('pending', 'approved', 'blocked') NOT NULL DEFAULT 'pending',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE spaces (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    venue_id BIGINT NOT NULL,
    space_type_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    
    price_per_hour DECIMAL(10, 2) NULL,
    price_per_day DECIMAL(10, 2) NULL,
    price_per_month DECIMAL(10, 2) NULL,
    
    open_hour TIME NULL DEFAULT '08:00:00',
    close_hour TIME NULL DEFAULT '18:00:00',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (space_type_id) REFERENCES space_types(id)
);

CREATE TABLE amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amenity_name VARCHAR(100) NOT NULL UNIQUE,
    icon_url VARCHAR(255) NULL
);

CREATE TABLE venue_amenities (
    venue_id BIGINT NOT NULL,
    amenity_id INT NOT NULL,
    
    PRIMARY KEY (venue_id, amenity_id),
    
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

CREATE TABLE space_managers (
    space_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    
    PRIMARY KEY (space_id, user_id),
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    space_id BIGINT NULL,
    
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    
    total_price DECIMAL(10, 2) NOT NULL,
    
    status ENUM(
        'pending_confirmation', -- Chờ chủ/mod duyệt
        'awaiting_payment',     -- Đã duyệt, chờ người dùng thanh toán
        'confirmed',            -- Đã thanh toán, đã xác nhận
        'cancelled',            -- Đã hủy (bởi user hoặc admin)
        'completed'             -- Đã hoàn thành (thời gian đặt đã qua)
    ) NOT NULL DEFAULT 'pending_confirmation',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL, 
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE SET NULL,
    
);

CREATE TABLE payments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT NOT NULL UNIQUE,
    
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NULL COMMENT 'VNPAY, MoMo, Stripe, Bank Transfer',
    transaction_id VARCHAR(255) NULL COMMENT 'Mã giao dịch từ cổng thanh toán',
    
    transaction_status ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    message TEXT NOT NULL,
    
    type ENUM('email', 'sms', 'app') NOT NULL DEFAULT 'app',
    
    is_read BOOLEAN DEFAULT FALSE,
    related_url VARCHAR(255) NULL, 
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE conversations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE conversation_participants (
    conversation_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    
    PRIMARY KEY (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT NOT NULL,
    sender_id BIGINT NOT NULL,
    message_body TEXT NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);




