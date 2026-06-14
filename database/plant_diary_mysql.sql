-- ============================================================
-- Plant Care Diary — DDL Script (MySQL translation of plant_diary_ddl_ORACLE.sql)
-- Target: MySQL 8.x / MariaDB 10.4+ (XAMPP)
-- The Oracle file (plant_diary_ddl_ORACLE.sql) remains the design document;
-- this file is what actually runs in dev.
-- ============================================================

-- Drop in reverse dependency order. FK_CHECKS off makes re-runs safe
-- even if something is half-created from a previous failed import.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS care_logs;
DROP TABLE IF EXISTS plants;
DROP TABLE IF EXISTS plant_types;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. ROLES  (catalogue table — admin-managed)
-- ============================================================
CREATE TABLE roles (
                       id           INT          AUTO_INCREMENT PRIMARY KEY,
                       name         VARCHAR(50)  NOT NULL,
                       is_active    TINYINT(1)   NOT NULL DEFAULT 1
                 COMMENT 'Flag: 1 = role active, 0 = disabled. Never delete roles for audit safety.',
                       active_from  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                           COMMENT 'Timestamp when the role was introduced into the system.',
                       active_until TIMESTAMP    NULL     DEFAULT NULL
                 COMMENT 'Timestamp when the role was deactivated. NULL = currently active.',

                       CONSTRAINT roles_name_uq    UNIQUE (name),
                       CONSTRAINT roles_active_chk CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catalogue of available user roles in the system.';

-- ============================================================
-- 2. USERS
-- created_by / updated_by are self-referencing FKs
-- ============================================================
CREATE TABLE users (
                       id         INT          AUTO_INCREMENT PRIMARY KEY,
                       nickname   VARCHAR(50)  NOT NULL,
                       password   VARCHAR(255) NOT NULL
                           COMMENT 'Hashed password — NEVERR store plaintext (bcrypt).',
                       email      VARCHAR(100) NOT NULL
                           COMMENT 'Used as login.',
                       created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                       created_by INT          NULL
               COMMENT 'GDPR: which user created this record. Self-ref FK. NULL = self-registered.',
                       updated_at TIMESTAMP    NULL     DEFAULT NULL,
                       updated_by INT          NULL
               COMMENT 'GDPR: which user last modified this record. Self-ref FK.',

                       CONSTRAINT users_nickname_uq UNIQUE (nickname),
                       CONSTRAINT users_email_uq    UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='All system users regardless of role. GDPR: created_by/updated_by track record authorship.';

-- Self-referencing FKs are added AFTER the table exists.
-- (MySQL can't see `users` from inside its own CREATE TABLE.)
ALTER TABLE users
    ADD CONSTRAINT users_created_by_fk
        FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    ADD CONSTRAINT users_updated_by_fk
        FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL;

-- ============================================================
-- 3. USER_ROLES  (association table — N:N between users & roles)
-- ============================================================
CREATE TABLE user_roles (
                            id         INT       AUTO_INCREMENT PRIMARY KEY,
                            user_id    INT       NOT NULL,
                            role_id    INT       NOT NULL,
                            granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                                COMMENT 'When the role was assigned to the user.',
                            revoked_at TIMESTAMP NULL     DEFAULT NULL
               COMMENT 'When the role was removed. NULL = currently assigned.',

                            CONSTRAINT user_roles_uq      UNIQUE (user_id, role_id),
                            CONSTRAINT user_roles_user_fk FOREIGN KEY (user_id)
                                REFERENCES users (id) ON DELETE CASCADE,
                            CONSTRAINT user_roles_role_fk FOREIGN KEY (role_id)
                                REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Association table assigning roles to users. Supports multiple roles per user.';

-- ============================================================
-- 4. PLANT_TYPES  (catalogue table — admin-managed)
-- ============================================================
CREATE TABLE plant_types (
                             id                     INT          AUTO_INCREMENT PRIMARY KEY,
                             name                   VARCHAR(100) NOT NULL,
                             description            VARCHAR(500) NULL,
                             watering_interval_days SMALLINT     NOT NULL
                                 COMMENT 'Recommended days between waterings. Used for contextual form validation.',
                             is_active              TINYINT(1)   NOT NULL DEFAULT 1
                           COMMENT '0 = hidden from users, 1 = available for selection.',

                             CONSTRAINT plant_types_name_uq      UNIQUE (name),
                             CONSTRAINT plant_types_active_chk   CHECK (is_active IN (0, 1)),
                             CONSTRAINT plant_types_interval_chk CHECK (watering_interval_days > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin-managed catalogue of plant types (e.g. Succulent, Tropical, Herb).';

-- ============================================================
-- 5. PLANTS  (user-owned domain table)
-- ============================================================
CREATE TABLE plants (
                        id            INT           AUTO_INCREMENT PRIMARY KEY,
                        user_id       INT           NOT NULL,
                        plant_type_id INT           NOT NULL,
                        name          VARCHAR(100)  NOT NULL
                            COMMENT 'User-given nickname for their plant (e.g. "My kitchen basil").',
                        notes         VARCHAR(1000) NULL,
                        created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

                        CONSTRAINT plants_user_fk FOREIGN KEY (user_id)
                            REFERENCES users (id) ON DELETE CASCADE,
                        CONSTRAINT plants_type_fk FOREIGN KEY (plant_type_id)
                            REFERENCES plant_types (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Plants owned by users. Each plant belongs to one user and one plant type.';

-- ============================================================
-- 6. CARE_LOGS  (diary entries per plant)
-- ============================================================
CREATE TABLE care_logs (
                           id        INT          AUTO_INCREMENT PRIMARY KEY,
                           plant_id  INT          NOT NULL,
                           action    VARCHAR(50)  NOT NULL
                               COMMENT 'Type of care action. Constrained to known values.',
                           notes     VARCHAR(500) NULL,
                           logged_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

                           CONSTRAINT care_logs_plant_fk   FOREIGN KEY (plant_id)
                               REFERENCES plants (id) ON DELETE CASCADE,
                           CONSTRAINT care_logs_action_chk CHECK (
                               action IN ('watering', 'fertilising', 'repotting', 'pruning', 'other')
)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Diary of care actions performed on a plant by its owner.';

-- ============================================================
-- Indexes for search & filtering (lists requirement)
-- Note: MySQL auto-creates an index on every FK column and every UNIQUE
-- constraint, so most of the original Oracle indexes are already covered.
-- Only logged_at needs a manually-added index for "filter by date range".
-- The four redundant ones are kept for parity with the design doc — they
-- cost a tiny bit of write speed but make the schema match 1:1.
-- ============================================================
CREATE INDEX idx_plants_user_id      ON plants    (user_id);
CREATE INDEX idx_plants_type_id      ON plants    (plant_type_id);
CREATE INDEX idx_care_logs_plant_id  ON care_logs (plant_id);
CREATE INDEX idx_care_logs_logged_at ON care_logs (logged_at);
CREATE INDEX idx_user_roles_user_id  ON user_roles(user_id);

-- ============================================================
-- Seed data — default roles
-- (active_from auto-fills via CURRENT_TIMESTAMP default)
-- ============================================================
INSERT INTO roles (name, is_active) VALUES
                                        ('guest', 1),
                                        ('user',  1),
                                        ('admin', 1);

-- Seed data — default plant types
INSERT INTO plant_types (name, description, watering_interval_days, is_active) VALUES
                                                                                   ('Succulent', 'Drought-tolerant plants with fleshy leaves.',          14, 1),
                                                                                   ('Tropical',  'Humidity-loving plants from tropical climates.',        3, 1),
                                                                                   ('Herb',      'Culinary or medicinal herbs.',                          2, 1),
                                                                                   ('Cactus',    'Desert plants requiring minimal watering.',            21, 1),
                                                                                   ('Fern',      'Shade-loving plants needing consistent moisture.',      2, 1);

COMMIT;
