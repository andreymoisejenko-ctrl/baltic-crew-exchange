CREATE TABLE listings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_id VARCHAR(24) NULL UNIQUE,
    type ENUM('crew','project') NOT NULL,
    status ENUM('pending','published','paused','closed','rejected') NOT NULL DEFAULT 'pending',
    title VARCHAR(180) NOT NULL,
    country VARCHAR(100) NOT NULL,
    city VARCHAR(120) NULL,
    industry VARCHAR(100) NOT NULL,
    specialisations TEXT NOT NULL,
    people_count SMALLINT UNSIGNED NULL,
    available_from DATE NULL,
    available_until DATE NULL,
    duration VARCHAR(120) NULL,
    experience TEXT NULL,
    certifications TEXT NULL,
    languages VARCHAR(255) NULL,
    mobility VARCHAR(500) NULL,
    rate_info VARCHAR(255) NULL,
    accommodation VARCHAR(255) NULL,
    description TEXT NULL,
    company_name VARCHAR(180) NOT NULL,
    registration_number VARCHAR(80) NULL,
    contact_name VARCHAR(160) NOT NULL,
    contact_email VARCHAR(190) NOT NULL,
    contact_phone VARCHAR(80) NULL,
    consent TINYINT(1) NOT NULL DEFAULT 0,
    is_demo TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at DATETIME NULL,
    INDEX idx_public (type, status, published_at),
    INDEX idx_contact_email (contact_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE interests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id BIGINT UNSIGNED NOT NULL,
    status ENUM('new','reviewing','introduced','closed','rejected') NOT NULL DEFAULT 'new',
    company_name VARCHAR(180) NOT NULL,
    contact_name VARCHAR(160) NOT NULL,
    contact_email VARCHAR(190) NOT NULL,
    contact_phone VARCHAR(80) NULL,
    message TEXT NULL,
    consent TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_interest_listing FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    INDEX idx_interest_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rate_limits (
    fingerprint CHAR(64) PRIMARY KEY,
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    window_started DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
