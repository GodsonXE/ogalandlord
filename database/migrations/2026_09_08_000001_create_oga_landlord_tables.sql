CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    phone_number VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NULL,
    role ENUM('SUPERADMIN', 'LANDLORD', 'CARETAKER', 'TENANT') NOT NULL DEFAULT 'TENANT',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    landlord_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(191) NOT NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT "Nigeria",
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_properties_landlord (landlord_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_caretaker_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    caretaker_id BIGINT UNSIGNED NOT NULL,
    can_manage_tickets BOOLEAN NOT NULL DEFAULT TRUE,
    can_view_finances BOOLEAN NOT NULL DEFAULT FALSE,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (caretaker_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_property_caretaker (property_id, caretaker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    property_id BIGINT UNSIGNED NOT NULL,
    unit_number VARCHAR(50) NOT NULL,
    apartment_type VARCHAR(100) NOT NULL,
    default_rent_amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT "NGN",
    is_occupied BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_units_property_occupied (property_id, is_occupied)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    unit_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    agreement_mode ENUM("GENERATE_AND_SIGN", "UPLOAD_EXISTING", "SKIP_AGREEMENT") NOT NULL,
    agreement_status ENUM(
        "DRAFT",
        "WAITING_FOR_TENANT_SIGNATURE",
        "SIGNED_OFFLINE",
        "ACTIVE_WITHOUT_CONTRACT",
        "FULLY_EXECUTED"
    ) NOT NULL DEFAULT "DRAFT",
    rent_amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT "NGN",
    rent_start_date DATE NOT NULL,
    rent_due_date DATE NOT NULL,
    emergency_contact_name VARCHAR(150) NOT NULL,
    emergency_contact_relationship VARCHAR(50) NOT NULL,
    emergency_contact_phone VARCHAR(30) NOT NULL,
    signing_token_hash VARCHAR(64) NULL UNIQUE,
    signing_token_expires_at TIMESTAMP NULL,
    generated_pdf_path VARCHAR(255) NULL,
    uploaded_agreement_path VARCHAR(255) NULL,
    document_sha256_hash VARCHAR(64) NULL,
    tenant_signature_type ENUM("DRAWN", "TYPED", "UPLOADED") NULL,
    tenant_signature_image_path VARCHAR(255) NULL,
    tenant_signed_at TIMESTAMP NULL,
    tenant_ip_address VARCHAR(45) NULL,
    tenant_user_agent VARCHAR(255) NULL,
    landlord_signed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_leases_due_date (rent_due_date),
    INDEX idx_leases_token_hash (signing_token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rent_reminder_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lease_id BIGINT UNSIGNED NOT NULL,
    dunning_phase VARCHAR(50) NOT NULL,
    days_to_due INT NOT NULL,
    recipient_email VARCHAR(191) NOT NULL,
    recipient_phone VARCHAR(30) NOT NULL,
    caretakers_cc_json JSON NULL,
    email_sent_successfully BOOLEAN NOT NULL DEFAULT FALSE,
    sms_sent_successfully BOOLEAN NOT NULL DEFAULT FALSE,
    dispatch_payload TEXT NULL,
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE,
    INDEX idx_reminder_lease_phase (lease_id, dunning_phase, sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rent_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    lease_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT "NGN",
    payment_method ENUM("ONLINE_GATEWAY", "BANK_TRANSFER", "CASH") NOT NULL,
    gateway_reference VARCHAR(191) NULL UNIQUE,
    payment_status ENUM("PENDING", "PAID", "FAILED") NOT NULL DEFAULT "PENDING",
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    covered_period_start DATE NOT NULL,
    covered_period_end DATE NOT NULL,
    receipt_number VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE,
    INDEX idx_payments_reference (gateway_reference),
    INDEX idx_payments_lease_status (lease_id, payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS processed_webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    idempotency_key VARCHAR(191) NOT NULL UNIQUE,
    gateway_provider VARCHAR(50) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload_hash VARCHAR(64) NOT NULL,
    processed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhooks_key (idempotency_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS maintenance_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    property_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(191) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM("LOW", "MEDIUM", "HIGH", "URGENT") NOT NULL DEFAULT "MEDIUM",
    status ENUM("REPORTED", "IN_REVIEW", "TECHNICIAN_DISPATCHED", "COMPLETED") NOT NULL DEFAULT "REPORTED",
    assigned_vendor_name VARCHAR(150) NULL,
    assigned_vendor_phone VARCHAR(30) NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tickets_property_status (property_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saas_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    landlord_id BIGINT UNSIGNED NOT NULL,
    billing_model ENUM("PER_UNIT", "PER_PROPERTY") NOT NULL DEFAULT "PER_UNIT",
    rate_per_unit DECIMAL(10, 2) NOT NULL DEFAULT 3000.00,
    rate_per_property DECIMAL(10, 2) NOT NULL DEFAULT 25000.00,
    currency VARCHAR(3) NOT NULL DEFAULT "NGN",
    next_billing_date DATE NOT NULL,
    status ENUM("ACTIVE", "PAST_DUE", "CANCELLED") NOT NULL DEFAULT "ACTIVE",
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;