-- ======================================================================
-- Dorfaki — MySQL Database Schema
-- ======================================================================
-- Run: mysql -u <user> -p dorfaki < database/schema.sql
-- ======================================================================

CREATE DATABASE IF NOT EXISTS `dorfaki` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `dorfaki`;

-- --------------------------------------------------------
-- Table: admin_users
-- Admin panel authentication credentials.
-- Default user: admin / admin (password is bcrypt-hashed)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`    VARCHAR(50)  NOT NULL COMMENT 'نام کاربری',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'رمز عبور经过 هش',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default admin user: admin / admin
-- password_hash is password_hash('admin', PASSWORD_DEFAULT)
INSERT INTO `admin_users` (`id`, `username`, `password_hash`)
VALUES (1, 'admin', '$2y$12$NLtqei.yWscCa55S./fwtefYcThGWEbOa7IKljiixAg8HPL5DT2oW')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- --------------------------------------------------------
-- Table: contact_info
-- Stores contact details displayed on the public website.
-- Fields left blank (NULL/empty) are hidden on the website.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_info` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tel`        VARCHAR(100) DEFAULT NULL COMMENT 'تلفن',
    `address`    VARCHAR(255) DEFAULT NULL COMMENT 'آدرس',
    `email`      VARCHAR(150) DEFAULT NULL COMMENT 'ایمیل',
    `telegram`   VARCHAR(100) DEFAULT NULL COMMENT 'شناسه تلگرام',
    `whatsapp`   VARCHAR(255) DEFAULT NULL COMMENT 'لینک واتساپ',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed a single row with empty values
INSERT INTO `contact_info` (`id`, `tel`, `address`, `email`, `telegram`, `whatsapp`)
VALUES (1, NULL, NULL, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- --------------------------------------------------------
-- Table: settings
-- Generic key-value store for website info and other settings.
-- Reserved for future sections: Website info, Settings, etc.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL COMMENT 'کلید تنظیم',
    `setting_value` TEXT DEFAULT NULL COMMENT 'مقدار تنظیم',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================
-- End of schema
-- ======================================================================

-- --------------------------------------------------------
-- Table: website_info
-- Stores website content settings editable from the admin panel.
-- Fields: title, header_subtitle, footer_subtitle, hero_title,
--         hero_subtitle, about_me, divar_merchant_id
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `website_info` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`          VARCHAR(200) DEFAULT NULL COMMENT 'عنوان سایت (هدر و فوتر)',
    `header_subtitle` VARCHAR(255) DEFAULT NULL COMMENT 'زیرنویس هدر',
    `footer_subtitle`  VARCHAR(255) DEFAULT NULL COMMENT 'زیرنویس فوتر',
    `hero_title`     VARCHAR(200) DEFAULT NULL COMMENT 'عنوان sezione هیرو',
    `hero_subtitle`  VARCHAR(255) DEFAULT NULL COMMENT 'زیرنویس sezione هیرو',
    `about_me`       TEXT DEFAULT NULL COMMENT 'درباره من (محتوا)',
    `divar_merchant_id` VARCHAR(100) DEFAULT NULL COMMENT 'شناسه تاجر دیوار (آخرین بخش URL)',
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed a single row with empty values
INSERT INTO `website_info` (`id`, `title`, `header_subtitle`, `footer_subtitle`, `hero_title`, `hero_subtitle`, `about_me`, `divar_merchant_id`)
VALUES (1, NULL, NULL, NULL, NULL, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE `id` = `id`;
