CREATE DATABASE IF NOT EXISTS smsallword CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smsallword;

DROP TABLE IF EXISTS affiliate_registrations;
DROP TABLE IF EXISTS affiliates;
DROP TABLE IF EXISTS sms_messages;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS service_countries;
DROP TABLE IF EXISTS countries;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS rentals;
DROP TABLE IF EXISTS deposits;
DROP TABLE IF EXISTS balances;
DROP TABLE IF EXISTS users;
DELETE FROM balances WHERE user_id = 8;
DELETE FROM users WHERE id = 8;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    verification_token VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    title VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    available_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    pending_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(200) NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    success_rate INT NOT NULL DEFAULT 95
) ENGINE=InnoDB;

CREATE TABLE countries (
    id INT PRIMARY KEY,
    country_name VARCHAR(80) NOT NULL,
    code VARCHAR(10) NOT NULL,
    region VARCHAR(40) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE service_countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    country_id INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    min_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    custom_min_price DECIMAL(10,2) DEFAULT NULL,
    custom_max_price DECIMAL(10,2) DEFAULT NULL,
    UNIQUE KEY uniq_service_country (service_id, country_id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (country_id) REFERENCES countries(id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    country VARCHAR(60) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    provider_order_id VARCHAR(60) DEFAULT NULL,
    cost DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
) ENGINE=InnoDB;

CREATE TABLE sms_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

CREATE TABLE deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit','withdraw') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    title VARCHAR(160) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    country VARCHAR(60) NOT NULL,
    status ENUM('active','expired') NOT NULL DEFAULT 'active',
    expires_at DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE affiliates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    promo_code VARCHAR(20) NOT NULL,
    referral_link VARCHAR(255) NOT NULL,
    total_earnings DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_registers INT NOT NULL DEFAULT 0,
    pending_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE affiliate_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    earnings DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id)
) ENGINE=INNODB;


ALTER TABLE orders ADD COLUMN provider_order_id VARCHAR(60) DEFAULT NULL AFTER phone_number;

INSERT INTO users (username, email, role, password_hash) VALUES
('demo_user', 'user@example.com', 'user', '$2y$10$demo'),
('admin_user', 'admin@example.com', 'admin', '$2y$10$demo');

INSERT INTO balances (user_id, available_balance, pending_balance) VALUES
(1, 3.29, 0.00),
(2, 120.00, 10.00);

-- Services seed moved to database/import_services.sql
INSERT INTO services (id, service_name, base_price, success_rate) VALUES
(907, 'Telegram', 0.40, 98),
(1012, 'WhatsApp', 0.55, 94),
(329, 'Facebook', 0.35, 96);

INSERT INTO countries (id, country_name, code, region) VALUES
(1, 'United States', 'US', NULL),
(53, 'Mexico', 'MX', NULL),
(128, 'Guadeloupe', 'GP', NULL),
(148, 'Anguilla', 'AI', NULL),
(2, 'United Kingdom', 'GB', NULL),
(3, 'Netherlands', 'NL', NULL),
(5, 'Latvia', 'LV', NULL),
(6, 'Sweden', 'SE', NULL),
(8, 'Portugal', 'PT', NULL),
(10, 'Estonia', 'EE', NULL),
(13, 'Romania', 'RO', NULL),
(19, 'Denmark', 'DK', NULL),
(21, 'Poland', 'PL', NULL),
(23, 'France', 'FR', NULL),
(24, 'Germany', 'DE', NULL),
(25, 'Ukraine', 'UA', NULL),
(32, 'Ireland', 'IE', NULL),
(37, 'Serbia', 'RS', NULL),
(47, 'Lithuania', 'LT', NULL),
(48, 'Croatia', 'HR', NULL),
(50, 'Austria', 'AT', NULL),
(51, 'Belarus', 'BY', NULL),
(55, 'Spain', 'ES', NULL),
(57, 'Slovenia', 'SI', NULL),
(75, 'Belgium', 'BE', NULL),
(76, 'Bulgaria', 'BG', NULL),
(77, 'Hungary', 'HU', NULL),
(78, 'Moldova', 'MD', NULL),
(79, 'Italy', 'IT', NULL),
(102, 'Greece', 'GR', NULL),
(104, 'Iceland', 'IS', NULL),
(112, 'Slovakia', 'SK', NULL),
(115, 'Monaco', 'MC', NULL),
(123, 'Albania', 'AL', NULL),
(130, 'Finland', 'FI', NULL),
(131, 'Luxembourg', 'LU', NULL),
(133, 'Montenegro', 'ME', NULL),
(134, 'Switzerland', 'CH', NULL),
(135, 'Norway', 'NO', NULL),
(149, 'Czech Republic', 'CZ', NULL),
(156, 'Aland Islands', 'AX', NULL),
(158, 'Gibraltar', 'GI', NULL),
(162, 'Bosnia', 'BA', NULL),
(164, 'Malta', 'MT', NULL),
(7, 'Kazakhstan', 'KZ', NULL),
(9, 'Indonesia', 'ID', NULL),
(11, 'Vietnam', 'VN', NULL),
(12, 'Philippines', 'PH', NULL),
(15, 'India', 'IN', NULL),
(18, 'Kyrgyzstan', 'KG', NULL),
(20, 'Malaysia', 'MY', NULL),
(29, 'Israel', 'IL', NULL),
(33, 'Cambodia', 'KH', NULL),
(34, 'Laos', 'LA', NULL),
(38, 'Yemen', 'YE', NULL),
(44, 'Uzbekistan', 'UZ', NULL),
(49, 'Iraq', 'IQ', NULL),
(52, 'Thailand', 'TH', NULL),
(54, 'Taiwan', 'TW', NULL),
(58, 'Bangladesh', 'BD', NULL),
(60, 'Turkey', 'TR', NULL),
(62, 'Pakistan', 'PK', NULL),
(67, 'Mongolia', 'MN', NULL),
(69, 'Afghanistan', 'AF', NULL),
(72, 'Cyprus', 'CY', NULL),
(74, 'Nepal', 'NP', NULL),
(88, 'Kuwait', 'KW', NULL),
(91, 'Oman', 'OM', NULL),
(92, 'Qatar', 'QA', NULL),
(95, 'Jordan', 'JO', NULL),
(98, 'Brunei', 'BN', NULL),
(101, 'Georgia', 'GE', NULL),
(114, 'Tajikistan', 'TJ', NULL),
(118, 'Armenia', 'AM', NULL),
(121, 'Lebanon', 'LB', NULL),
(126, 'Bhutan', 'BT', NULL),
(127, 'Maldives', 'MV', NULL),
(129, 'Turkmenistan', 'TM', NULL),
(141, 'Singapore', 'SG', NULL),
(151, 'Hong Kong', 'HK', NULL),
(154, 'Azerbaijan', 'AZ', NULL),
(157, 'Japan', 'JP', NULL),
(163, 'Macao', 'MO', NULL),
(14, 'Nigeria', 'NG', NULL),
(16, 'Kenya', 'KE', NULL),
(27, 'Tanzania', 'TZ', NULL),
(30, 'Madagascar', 'MG', NULL),
(31, 'Egypt', 'EG', NULL),
(36, 'Gambia', 'GM', NULL),
(41, 'Morocco', 'MA', NULL),
(42, 'Ghana', 'GH', NULL),
(45, 'Cameroon', 'CM', NULL),
(46, 'Chad', 'TD', NULL),
(56, 'Algeria', 'DZ', NULL),
(59, 'Senegal', 'SN', NULL),
(63, 'Guinea', 'GN', NULL),
(64, 'Mali', 'ML', NULL),
(66, 'Ethiopia', 'ET', NULL),
(70, 'Uganda', 'UG', NULL),
(71, 'Angola', 'AO', NULL),
(73, 'Mozambique', 'MZ', NULL),
(82, 'Tunisia', 'TN', NULL),
(86, 'Zimbabwe', 'ZW', NULL),
(87, 'Togo', 'TG', NULL),
(90, 'Swaziland', 'SZ', NULL),
(94, 'Mauritania', 'MR', NULL),
(96, 'Burundi', 'BI', NULL),
(97, 'Benin', 'BJ', NULL),
(99, 'Botswana', 'BW', NULL),
(105, 'Comoros', 'KM', NULL),
(106, 'Liberia', 'LR', NULL),
(107, 'Lesotho', 'LS', NULL),
(108, 'Malawi', 'MW', NULL),
(109, 'Namibia', 'NA', NULL),
(110, 'Niger', 'NE', NULL),
(111, 'Rwanda', 'RW', NULL),
(117, 'Zambia', 'ZM', NULL),
(119, 'Somalia', 'SO', NULL),
(122, 'Gabon', 'GA', NULL),
(125, 'Mauritius', 'MU', NULL),
(132, 'Djibouti', 'DJ', NULL),
(137, 'Eritrea', 'ER', NULL),
(139, 'Seychelles', 'SC', NULL),
(153, 'South Africa', 'ZA', NULL),
(22, 'United States (Virtual)', 'US_V', NULL),
(35, 'Haiti', 'HT', NULL),
(39, 'Colombia', 'CO', NULL),
(43, 'Argentina', 'AR', NULL),
(61, 'Peru', 'PE', NULL),
(65, 'Venezuela', 'VE', NULL),
(68, 'Brazil', 'BR', NULL),
(80, 'Paraguay', 'PY', NULL),
(84, 'Bolivia', 'BO', NULL),
(89, 'Ecuador', 'EC', NULL),
(103, 'Guyana', 'GY', NULL),
(113, 'Suriname', 'SR', NULL),
(120, 'Chile', 'CL', NULL),
(124, 'Uruguay', 'UY', NULL),
(138, 'Aruba', 'AW', NULL),
(136, 'Australia (Virtual)', 'AU_V', NULL),
(140, 'Fiji', 'FJ', NULL),
(81, 'Honduras', 'HN', NULL),
(83, 'Nicaragua', 'NI', NULL),
(85, 'Guatemala', 'GT', NULL),
(93, 'Panama', 'PA', NULL),
(100, 'Belize', 'BZ', NULL),
(142, 'Jamaica', 'JM', NULL),
(143, 'Barbados', 'BB', NULL),
(144, 'Bahamas', 'BS', NULL),
(145, 'Dominica', 'DM', NULL),
(146, 'Grenada', 'GD', NULL),
(147, 'Montserrat', 'MS', NULL);

INSERT INTO service_countries (service_id, country_id, stock, min_price, max_price) VALUES
(907, 1, 15961, 0.12, 0.12),
(907, 2, 140038, 0.10, 0.11),
(907, 5, 220, 0.11, 0.11),
(907, 6, 2877, 0.11, 0.11),
(907, 7, 102, 0.11, 0.11),
(1012, 1, 8000, 0.35, 0.45),
(1012, 11, 900, 0.18, 0.20),
(1012, 9, 4500, 0.15, 0.18),
(1012, 68, 1200, 0.22, 0.26),
(329, 1, 5000, 0.20, 0.22),
(329, 12, 2100, 0.12, 0.14),
(329, 52, 1300, 0.13, 0.15),
(329, 15, 9800, 0.09, 0.12);

INSERT INTO orders (user_id, service_id, country, phone_number, cost, status) VALUES
(1, 907, 'United States', '+12025550123', 0.40, 'pending'),
(1, 1012, 'United Kingdom', '+447911123456', 0.55, 'completed');

INSERT INTO sms_messages (order_id, code, message) VALUES
(2, '129883', 'Your WhatsApp verification code is 129883');

INSERT INTO deposits (user_id, amount, method, created_at) VALUES
(1, 4.21, 'Cryptomus', '2025-11-12 04:55:51'),
(1, 3.90, 'Cryptomus', '2025-03-07 02:14:51'),
(1, 4.00, 'Cryptomus', '2024-07-25 03:12:35');

INSERT INTO rentals (user_id, phone_number, country, status, expires_at) VALUES
(1, '+12025550999', 'United States', 'active', '2026-01-20');

INSERT INTO affiliates (user_id, promo_code, referral_link, total_earnings, total_registers, pending_balance) VALUES
(1, 'HYB0RWYQ', 'https://example.com/r/HYB0RWYQ', 0.00, 0, 0.00);

INSERT INTO affiliate_registrations (affiliate_id, username, earnings, created_at) VALUES
(1, 'ref_user1', 0.00, '2025-11-20 09:15:00');
SELECT * FROM services;
