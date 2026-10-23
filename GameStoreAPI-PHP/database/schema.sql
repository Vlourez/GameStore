-- =====================================================================
-- GameStoreAPI - Schema MySQL (compatível com XAMPP / phpMyAdmin)
-- Importe este arquivo pelo phpMyAdmin (aba "Importar") ou via:
--   mysql -u root -p < schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS gamestore_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gamestore_db;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id           CHAR(36)      NOT NULL PRIMARY KEY,
    name         VARCHAR(100)  NOT NULL,
    description  VARCHAR(500)  NOT NULL DEFAULT '',
    slug         VARCHAR(100)  NOT NULL,
    created_at   DATETIME      NOT NULL,
    updated_at   DATETIME      NULL,
    is_deleted   TINYINT(1)    NOT NULL DEFAULT 0,
    UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- customers
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id             CHAR(36)      NOT NULL PRIMARY KEY,
    full_name      VARCHAR(100)  NOT NULL,
    email          VARCHAR(200)  NOT NULL,
    password_hash  VARCHAR(255)  NOT NULL,
    avatar_url     VARCHAR(500)  NULL,
    birth_date     DATE          NULL,
    is_active      TINYINT(1)    NOT NULL DEFAULT 1,
    role           VARCHAR(20)   NOT NULL DEFAULT 'Customer',
    created_at     DATETIME      NOT NULL,
    updated_at     DATETIME      NULL,
    is_deleted     TINYINT(1)    NOT NULL DEFAULT 0,
    UNIQUE KEY uq_customers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- games
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS games (
    id                CHAR(36)       NOT NULL PRIMARY KEY,
    title             VARCHAR(200)   NOT NULL,
    description       VARCHAR(5000)  NOT NULL DEFAULT '',
    developer         VARCHAR(100)   NOT NULL,
    publisher         VARCHAR(100)   NOT NULL,
    price             DECIMAL(10,2)  NOT NULL,
    discount_price    DECIMAL(10,2)  NULL,
    release_date      DATE           NOT NULL,
    cover_image_url   VARCHAR(500)   NULL,
    status            TINYINT        NOT NULL DEFAULT 2, -- 1=ComingSoon 2=Available 3=Discontinued
    tags              TEXT           NULL,               -- JSON-encoded array of strings
    category_id       CHAR(36)       NOT NULL,
    created_at        DATETIME       NOT NULL,
    updated_at        DATETIME       NULL,
    is_deleted        TINYINT(1)     NOT NULL DEFAULT 0,
    KEY idx_games_category (category_id),
    CONSTRAINT fk_games_category FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- purchases
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchases (
    id              CHAR(36)      NOT NULL PRIMARY KEY,
    customer_id     CHAR(36)      NOT NULL,
    status          TINYINT       NOT NULL DEFAULT 1, -- 1=Pending 2=Completed 3=Refunded 4=Cancelled
    total_amount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method  VARCHAR(50)   NULL,
    purchased_at    DATETIME      NOT NULL,
    created_at      DATETIME      NOT NULL,
    updated_at      DATETIME      NULL,
    is_deleted      TINYINT(1)    NOT NULL DEFAULT 0,
    KEY idx_purchases_customer (customer_id),
    CONSTRAINT fk_purchases_customer FOREIGN KEY (customer_id) REFERENCES customers (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- purchase_items
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchase_items (
    id           CHAR(36)      NOT NULL PRIMARY KEY,
    purchase_id  CHAR(36)      NOT NULL,
    game_id      CHAR(36)      NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    created_at   DATETIME      NOT NULL,
    updated_at   DATETIME      NULL,
    is_deleted   TINYINT(1)    NOT NULL DEFAULT 0,
    UNIQUE KEY uq_purchase_items (purchase_id, game_id),
    KEY idx_purchase_items_game (game_id),
    CONSTRAINT fk_purchase_items_purchase FOREIGN KEY (purchase_id) REFERENCES purchases (id),
    CONSTRAINT fk_purchase_items_game FOREIGN KEY (game_id) REFERENCES games (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- library_entries (games owned by a customer)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS library_entries (
    id                       CHAR(36)  NOT NULL PRIMARY KEY,
    customer_id              CHAR(36)  NOT NULL,
    game_id                  CHAR(36)  NOT NULL,
    acquired_at              DATETIME  NOT NULL,
    last_played_at           DATETIME  NULL,
    total_playtime_minutes   INT       NOT NULL DEFAULT 0,
    created_at               DATETIME  NOT NULL,
    updated_at               DATETIME  NULL,
    is_deleted               TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_library (customer_id, game_id),
    KEY idx_library_game (game_id),
    CONSTRAINT fk_library_customer FOREIGN KEY (customer_id) REFERENCES customers (id),
    CONSTRAINT fk_library_game FOREIGN KEY (game_id) REFERENCES games (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- wishlist_entries
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist_entries (
    id                   CHAR(36)  NOT NULL PRIMARY KEY,
    customer_id          CHAR(36)  NOT NULL,
    game_id              CHAR(36)  NOT NULL,
    notify_on_discount   TINYINT(1) NOT NULL DEFAULT 1,
    added_at             DATETIME  NOT NULL,
    created_at           DATETIME  NOT NULL,
    updated_at           DATETIME  NULL,
    is_deleted           TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_wishlist (customer_id, game_id),
    KEY idx_wishlist_game (game_id),
    CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES customers (id),
    CONSTRAINT fk_wishlist_game FOREIGN KEY (game_id) REFERENCES games (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- reviews
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id              CHAR(36)      NOT NULL PRIMARY KEY,
    customer_id     CHAR(36)      NOT NULL,
    game_id         CHAR(36)      NOT NULL,
    rating          TINYINT       NOT NULL,
    title           VARCHAR(100)  NOT NULL,
    body            VARCHAR(2000) NOT NULL,
    is_recommended  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at      DATETIME      NOT NULL,
    updated_at      DATETIME      NULL,
    is_deleted      TINYINT(1)    NOT NULL DEFAULT 0,
    UNIQUE KEY uq_reviews (customer_id, game_id),
    KEY idx_reviews_game (game_id),
    CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES customers (id),
    CONSTRAINT fk_reviews_game FOREIGN KEY (game_id) REFERENCES games (id),
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- achievements
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS achievements (
    id           CHAR(36)      NOT NULL PRIMARY KEY,
    customer_id  CHAR(36)      NOT NULL,
    type         TINYINT       NOT NULL, -- 1=FirstPurchase 2=ReviewWriter 3=Collector 4=BigSpender 5=EarlyAdopter
    name         VARCHAR(100)  NOT NULL,
    description  VARCHAR(300)  NOT NULL,
    earned_at    DATETIME      NOT NULL,
    created_at   DATETIME      NOT NULL,
    updated_at   DATETIME      NULL,
    is_deleted   TINYINT(1)    NOT NULL DEFAULT 0,
    UNIQUE KEY uq_achievements (customer_id, type),
    CONSTRAINT fk_achievements_customer FOREIGN KEY (customer_id) REFERENCES customers (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
