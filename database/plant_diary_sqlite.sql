-- ============================================================
-- Plant Care Diary — schemat dla SQLite
-- (konwersja z plant_diary_mysql.sql; uzywany przy wdrozeniu w kontenerze na Azure)
-- ============================================================

PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS care_logs;
DROP TABLE IF EXISTS plants;
DROP TABLE IF EXISTS plant_types;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

-- 1. ROLES
CREATE TABLE roles (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         VARCHAR(50)  NOT NULL,
    is_active    INTEGER      NOT NULL DEFAULT 1,
    active_from  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active_until TIMESTAMP    NULL DEFAULT NULL,
    CONSTRAINT roles_name_uq    UNIQUE (name),
    CONSTRAINT roles_active_chk CHECK (is_active IN (0, 1))
);

-- 2. USERS (samoodwolujace sie klucze obce zdefiniowane w srodku tabeli)
CREATE TABLE users (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    nickname   VARCHAR(50)  NOT NULL,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER      NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    updated_by INTEGER      NULL,
    CONSTRAINT users_nickname_uq UNIQUE (nickname),
    CONSTRAINT users_email_uq    UNIQUE (email),
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
);

-- 3. USER_ROLES
CREATE TABLE user_roles (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER   NOT NULL,
    role_id    INTEGER   NOT NULL,
    granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT user_roles_uq UNIQUE (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

-- 4. PLANT_TYPES
CREATE TABLE plant_types (
    id                     INTEGER PRIMARY KEY AUTOINCREMENT,
    name                   VARCHAR(100) NOT NULL,
    description            VARCHAR(500) NULL,
    watering_interval_days INTEGER      NOT NULL,
    is_active              INTEGER      NOT NULL DEFAULT 1,
    CONSTRAINT plant_types_name_uq      UNIQUE (name),
    CONSTRAINT plant_types_active_chk   CHECK (is_active IN (0, 1)),
    CONSTRAINT plant_types_interval_chk CHECK (watering_interval_days > 0)
);

-- 5. PLANTS
CREATE TABLE plants (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id       INTEGER       NOT NULL,
    plant_type_id INTEGER       NOT NULL,
    name          VARCHAR(100)  NOT NULL,
    notes         VARCHAR(1000) NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (plant_type_id) REFERENCES plant_types (id) ON DELETE RESTRICT
);

-- 6. CARE_LOGS
CREATE TABLE care_logs (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    plant_id  INTEGER      NOT NULL,
    action    VARCHAR(50)  NOT NULL,
    notes     VARCHAR(500) NULL,
    logged_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants (id) ON DELETE CASCADE,
    CONSTRAINT care_logs_action_chk CHECK (
        action IN ('watering', 'fertilising', 'repotting', 'pruning', 'other')
    )
);

-- Indeksy pod wyszukiwanie i filtrowanie
CREATE INDEX idx_plants_user_id      ON plants    (user_id);
CREATE INDEX idx_plants_type_id      ON plants    (plant_type_id);
CREATE INDEX idx_care_logs_plant_id  ON care_logs (plant_id);
CREATE INDEX idx_care_logs_logged_at ON care_logs (logged_at);
CREATE INDEX idx_user_roles_user_id  ON user_roles(user_id);

-- Dane startowe — role
INSERT INTO roles (name, is_active) VALUES
    ('guest', 1),
    ('user',  1),
    ('admin', 1);

-- Dane startowe — typy roslin
INSERT INTO plant_types (name, description, watering_interval_days, is_active) VALUES
    ('Succulent', 'Drought-tolerant plants with fleshy leaves.',      14, 1),
    ('Tropical',  'Humidity-loving plants from tropical climates.',    3, 1),
    ('Herb',      'Culinary or medicinal herbs.',                      2, 1),
    ('Cactus',    'Desert plants requiring minimal watering.',        21, 1),
    ('Fern',      'Shade-loving plants needing consistent moisture.',  2, 1);
