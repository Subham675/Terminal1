-- ── Migration 004: Guest Reviews & Feedback (Compliments and Complaints) ──
CREATE TABLE IF NOT EXISTS reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NULL,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(180) NULL,
    type            VARCHAR(20) DEFAULT 'compliment',
    rating          TINYINT NOT NULL DEFAULT 5,
    title           VARCHAR(160) NULL,
    content         TEXT NOT NULL,
    visit_date      DATE NULL,
    status          VARCHAR(20) DEFAULT 'approved',
    admin_reply     TEXT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reviews_type (type),
    INDEX idx_reviews_status (status),
    INDEX idx_reviews_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional initial seed data if table is empty
INSERT INTO reviews (name, type, rating, title, content, visit_date, status, admin_reply)
SELECT 'Priyanka Sen', 'compliment', 5, 'An Unforgettable Culinary Symphony',
       'The Kashmir Polao paired with the signature Dhonkami Chicken 4.0 was nothing short of perfection. The candle-lit ambiance and warm hospitality transported us straight to an old-world European bistro.',
       CURDATE() - INTERVAL 3 DAY, 'approved', 'Thank you for your gracious words, Priyanka. We are honored to have hosted your evening.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM reviews LIMIT 1);

INSERT INTO reviews (name, type, rating, title, content, visit_date, status, admin_reply)
SELECT 'Anirban Mukherjee', 'complaint', 3, 'Appetizer Timing on Saturday Evening',
       'The starters took nearly 40 minutes to arrive during peak Saturday dinner rush. Although the flavors were stellar once served, beverage refills and bread service were notably delayed.',
       CURDATE() - INTERVAL 5 DAY, 'approved', 'Dear Anirban, we sincerely apologize for the delay during Saturday peak hours. Our floor manager has reviewed table pacing with kitchen staff to ensure seamless service.'
FROM DUAL WHERE (SELECT COUNT(*) FROM reviews) < 2;

INSERT INTO reviews (name, type, rating, title, content, visit_date, status, admin_reply)
SELECT 'Dr. S. Roy Chowdhury', 'compliment', 5, 'Exemplary Craftsmanship in Cooch Behar',
       'Terminal 1 has elevated the dining standards of North Bengal. Every detail, from the textured menu parchment to the curated dim sum presentation, speaks of true culinary passion.',
       CURDATE() - INTERVAL 8 DAY, 'approved', NULL
FROM DUAL WHERE (SELECT COUNT(*) FROM reviews) < 3;
