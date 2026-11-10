-- Mirror of plugin schema (dbDelta is the runtime source of truth).
-- Prefix is environment-specific; replace wp_ if your table_prefix differs.

CREATE TABLE IF NOT EXISTS wp_harness_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(191) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
