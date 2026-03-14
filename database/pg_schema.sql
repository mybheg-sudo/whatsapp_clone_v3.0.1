-- PostgreSQL Schema for MYBHEG CRM
-- MySQL'den taşınan tablolar

-- Users tablosu (login için)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contacts tablosu
CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messages tablosu
CREATE TABLE IF NOT EXISTS messages (
    id SERIAL PRIMARY KEY,
    message_id VARCHAR(255) UNIQUE,
    contact_id INT NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    direction VARCHAR(10) NOT NULL CHECK (direction IN ('incoming', 'outgoing')),
    type VARCHAR(50) NOT NULL DEFAULT 'text',
    content TEXT,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    raw_data TEXT
);

-- Orders tablosu (Shopify siparişleri)
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    order_id VARCHAR(100),
    shopify_order_id VARCHAR(100),
    customer_name VARCHAR(255),
    customer_phone VARCHAR(50),
    email VARCHAR(255),
    total_price DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'TRY',
    status VARCHAR(50) DEFAULT 'pending',
    address TEXT,
    note TEXT,
    line_items TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    first_message_sent BOOLEAN DEFAULT FALSE,
    confirmed_at TIMESTAMP,
    cancelled_at TIMESTAMP
);

-- İndeksler
CREATE INDEX IF NOT EXISTS idx_contacts_phone ON contacts(phone);
CREATE INDEX IF NOT EXISTS idx_messages_contact_id ON messages(contact_id);
CREATE INDEX IF NOT EXISTS idx_messages_timestamp ON messages(timestamp);
CREATE INDEX IF NOT EXISTS idx_messages_direction ON messages(direction);
CREATE INDEX IF NOT EXISTS idx_orders_shopify_id ON orders(shopify_order_id);
CREATE INDEX IF NOT EXISTS idx_orders_phone ON orders(customer_phone);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
