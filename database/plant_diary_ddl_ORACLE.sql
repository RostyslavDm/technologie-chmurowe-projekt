-- ============================================================
-- Plant Care Diary — DDL Script
-- Target: Oracle Database (compatible with Oracle Data Modeler)
-- !!! In reality was used inly for the eneral idea of the database
-- since I was most familiar with Oracle at the moment of making it
-- ============================================================

-- Drop tables in reverse dependency order (safe re-run)
DROP TABLE care_logs   CASCADE CONSTRAINTS PURGE;
DROP TABLE plants      CASCADE CONSTRAINTS PURGE;
DROP TABLE plant_types CASCADE CONSTRAINTS PURGE;
DROP TABLE user_roles  CASCADE CONSTRAINTS PURGE;
DROP TABLE users       CASCADE CONSTRAINTS PURGE;
DROP TABLE roles       CASCADE CONSTRAINTS PURGE;

-- ============================================================
-- 1. ROLES  (catalogue table — admin-managed)
-- ============================================================
CREATE TABLE roles (
                       id           NUMBER          GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                       name         VARCHAR2(50)    NOT NULL,
                       is_active    NUMBER(1)       DEFAULT 1 NOT NULL,   -- 0 = inactive, 1 = active
                       active_from  TIMESTAMP       DEFAULT SYSTIMESTAMP NOT NULL,
                       active_until TIMESTAMP       NULL,                  -- NULL = still active

                       CONSTRAINT roles_name_uq    UNIQUE (name),
                       CONSTRAINT roles_active_chk CHECK (is_active IN (0, 1))
);

-- COMMENT ON TABLE  roles             IS 'Catalogue of available user roles in the system.';
-- COMMENT ON COLUMN roles.is_active   IS 'Flag: 1 = role active, 0 = disabled. Never delete roles for audit safety.';
-- COMMENT ON COLUMN roles.active_from IS 'Timestamp when the role was introduced into the system.';
-- COMMENT ON COLUMN roles.active_until IS 'Timestamp when the role was deactivated. NULL means currently active.';

-- ============================================================
-- 2. USERS
-- created_by / updated_by are self-referencing FKs
-- ============================================================
CREATE TABLE users (
                       id          NUMBER          GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                       nickname       VARCHAR2(50)    NOT NULL,
                       password    VARCHAR2(255)   NOT NULL,   -- HAS TO BE HASHED (bcrypt?)
                       email       VARCHAR2(100)   NOT NULL,   -- USED AS LOGIN
                       created_at  TIMESTAMP       DEFAULT SYSTIMESTAMP NOT NULL,
                       created_by  NUMBER          NULL,       -- FK to users.id (self-ref), NULL if self-registered
                       updated_at  TIMESTAMP       NULL,
                       updated_by  NUMBER          NULL,       -- FK to users.id (self-ref)

                       CONSTRAINT users_nickname_uq   UNIQUE (nickname),
                       CONSTRAINT users_email_uq   UNIQUE (email),
                       CONSTRAINT users_created_by_fk FOREIGN KEY (created_by)
                           REFERENCES users (id) ON DELETE SET NULL,
                       CONSTRAINT users_updated_by_fk FOREIGN KEY (updated_by)
                           REFERENCES users (id) ON DELETE SET NULL
);

COMMENT ON TABLE  users            IS 'All system users regardless of role. GDPR: created_by/updated_by track record authorship.';
COMMENT ON COLUMN users.password   IS 'Hashed password — never store plaintext.';
COMMENT ON COLUMN users.created_by IS 'GDPR: which user created this record. Self-referencing FK. NULL = self-registered.';
COMMENT ON COLUMN users.updated_by IS 'GDPR: which user last modified this record. Self-referencing FK.';

-- ============================================================
-- 3. USER_ROLES  (association table — N:N between users & roles)
-- ============================================================
CREATE TABLE user_roles (
                            id          NUMBER      GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                            user_id     NUMBER      NOT NULL,
                            role_id     NUMBER      NOT NULL,
                            granted_at  TIMESTAMP   DEFAULT SYSTIMESTAMP NOT NULL,
                            revoked_at  TIMESTAMP   NULL,   -- NULL = role still active for this user

                            CONSTRAINT user_roles_user_fk FOREIGN KEY (user_id)
                                REFERENCES users (id) ON DELETE CASCADE,
                            CONSTRAINT user_roles_role_fk FOREIGN KEY (role_id)
                                REFERENCES roles (id) ON DELETE CASCADE,
                            CONSTRAINT user_roles_uq UNIQUE (user_id, role_id)   -- one active assignment per combo
);

COMMENT ON TABLE  user_roles            IS 'Association table assigning roles to users. Supports multiple roles per user.';
COMMENT ON COLUMN user_roles.granted_at IS 'When the role was assigned to the user.';
COMMENT ON COLUMN user_roles.revoked_at IS 'When the role was removed. NULL = currently assigned.';

-- ============================================================
-- 4. PLANT_TYPES  (catalogue table — admin-managed)
-- ============================================================
CREATE TABLE plant_types (
                             id                      NUMBER          GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                             name                    VARCHAR2(100)   NOT NULL,
                             description             VARCHAR2(500)   NULL,
                             watering_interval_days  NUMBER(4)       NOT NULL,   -- used for contextual validation
                             is_active               NUMBER(1)       DEFAULT 1 NOT NULL,

                             CONSTRAINT plant_types_name_uq    UNIQUE (name),
                             CONSTRAINT plant_types_active_chk CHECK (is_active IN (0, 1)),
                             CONSTRAINT plant_types_interval_chk CHECK (watering_interval_days > 0)
);
--
-- COMMENT ON TABLE  plant_types                          IS 'Admin-managed catalogue of plant types (e.g. Succulent, Tropical, Herb).';
-- COMMENT ON COLUMN plant_types.watering_interval_days  IS 'Recommended days between waterings. Used for contextual form validation.';
-- COMMENT ON COLUMN plant_types.is_active               IS '0 = hidden from users, 1 = available for selection.';

-- ============================================================
-- 5. PLANTS  (user-owned domain table)
-- ============================================================
CREATE TABLE plants (
                        id            NUMBER          GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                        user_id       NUMBER          NOT NULL,
                        plant_type_id NUMBER          NOT NULL,
                        name          VARCHAR2(100)   NOT NULL,
                        notes         VARCHAR2(1000)  NULL,
                        created_at    TIMESTAMP       DEFAULT SYSTIMESTAMP NOT NULL,

                        CONSTRAINT plants_user_fk  FOREIGN KEY (user_id)
                            REFERENCES users (id) ON DELETE CASCADE,
                        CONSTRAINT plants_type_fk  FOREIGN KEY (plant_type_id)
                            REFERENCES plant_types (id) ON DELETE RESTRICT
);
--
-- COMMENT ON TABLE  plants      IS 'Plants owned by users. Each plant belongs to one user and one plant type.';
-- COMMENT ON COLUMN plants.name IS 'User-given nickname for their plant (e.g. "My kitchen basil").';

-- ============================================================
-- 6. CARE_LOGS  (diary entries per plant)
-- ============================================================
CREATE TABLE care_logs (
                           id        NUMBER          GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                           plant_id  NUMBER          NOT NULL,
                           action    VARCHAR2(50)    NOT NULL,   -- 'watering', 'fertilising', 'repotting'
                           notes     VARCHAR2(500)   NULL,
                           logged_at TIMESTAMP       DEFAULT SYSTIMESTAMP NOT NULL,

                           CONSTRAINT care_logs_plant_fk   FOREIGN KEY (plant_id)
                               REFERENCES plants (id) ON DELETE CASCADE,
                           CONSTRAINT care_logs_action_chk CHECK (
                               action IN ('watering', 'fertilising', 'repotting', 'pruning', 'other')
)
    );

-- COMMENT ON TABLE  care_logs        IS 'Diary of care actions performed on a plant by its owner.';
-- COMMENT ON COLUMN care_logs.action IS 'Type of care action. Constrained to known values.';

-- ============================================================
-- Seed data — default roles
-- ============================================================
INSERT INTO roles (name, is_active, active_from)
VALUES ('guest', 1, SYSTIMESTAMP);
INSERT INTO roles (name, is_active, active_from)
VALUES ('user', 1, SYSTIMESTAMP);
INSERT INTO roles (name, is_active, active_from)
VALUES ('admin', 1, SYSTIMESTAMP);

-- Seed data — default plant types
INSERT INTO plant_types (name, description, watering_interval_days, is_active)
VALUES ('Succulent', 'Drought-tolerant plants with fleshy leaves.', 14, 1);
INSERT INTO plant_types (name, description, watering_interval_days, is_active)
VALUES ('Tropical', 'Humidity-loving plants from tropical climates.', 3, 1);
INSERT INTO plant_types (name, description, watering_interval_days, is_active)
VALUES ('Herb', 'Culinary or medicinal herbs.', 2, 1);
INSERT INTO plant_types (name, description, watering_interval_days, is_active)
VALUES ('Cactus', 'Desert plants requiring minimal watering.', 21, 1);
INSERT INTO plant_types (name, description, watering_interval_days, is_active)
VALUES ('Fern', 'Shade-loving plants needing consistent moisture.', 2, 1);

COMMIT;

-- ============================================================
-- Useful indexes for search & filtering (lists requirement)
-- ============================================================
CREATE INDEX idx_plants_user_id      ON plants    (user_id);
CREATE INDEX idx_plants_type_id      ON plants    (plant_type_id);
CREATE INDEX idx_care_logs_plant_id  ON care_logs (plant_id);
CREATE INDEX idx_care_logs_logged_at ON care_logs (logged_at);
CREATE INDEX idx_user_roles_user_id  ON user_roles(user_id);
