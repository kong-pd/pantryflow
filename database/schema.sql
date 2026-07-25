CREATE DATABASE IF NOT EXISTS pantryflow
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pantryflow;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS client_requests;
DROP TABLE IF EXISTS food_items;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE food_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    expiry_date DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_food_items_name (name),
    INDEX idx_food_items_quantity (quantity),
    INDEX idx_food_items_expiry_date (expiry_date)
) ENGINE=InnoDB;

CREATE TABLE client_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    contact VARCHAR(30) NOT NULL,
    pickup_date DATE NOT NULL,
    food_item_id INT UNSIGNED NOT NULL,
    requested_qty INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_requests_food_item (food_item_id),
    INDEX idx_requests_pickup_date (pickup_date),
    CONSTRAINT chk_requests_quantity CHECK (requested_qty > 0),
    CONSTRAINT fk_requests_food_item
        FOREIGN KEY (food_item_id)
        REFERENCES food_items (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO food_items (name, quantity, expiry_date) VALUES
    ('Rice', 20, '2027-12-31'),
    ('Canned Beans', 12, NULL),
    ('Cooking Oil', 4, '2027-09-30'),
    ('Milk Powder', 6, '2025-12-31'),
    ('Pasta', 0, '2027-11-30'),
    ('Oatmeal', 8, NULL);

