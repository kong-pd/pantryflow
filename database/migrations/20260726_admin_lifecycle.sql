USE pantryflow;

ALTER TABLE food_items
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER expiry_date,
    ADD COLUMN archived_at TIMESTAMP NULL AFTER is_active,
    ADD INDEX idx_food_items_active (is_active);

ALTER TABLE client_requests
    ADD COLUMN status ENUM('pending', 'rejected') NOT NULL DEFAULT 'pending' AFTER requested_qty,
    ADD COLUMN reviewed_at TIMESTAMP NULL AFTER status,
    ADD INDEX idx_requests_status (status);

