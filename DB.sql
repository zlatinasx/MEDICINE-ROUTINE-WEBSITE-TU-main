
CREATE DATABASE IF NOT EXISTS medicine_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE medicine_db;

-- Drop in FK-safe order (if re-importing)
DROP TABLE IF EXISTS medicine_taken;
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS medicine_routines;
DROP TABLE IF EXISTS users;

-- -------------------------
-- USERS
-- -------------------------
CREATE TABLE users (
  id         INT            NOT NULL AUTO_INCREMENT,
  username   VARCHAR(100)   NOT NULL,
  email      VARCHAR(255)   NOT NULL,
  age        INT            NOT NULL,
  password   VARCHAR(255)   NOT NULL,
  created_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- MEDICINE ROUTINES
--  mode='one'       -> use scheduled_date (DATETIME)
--  mode='recurring' -> use scheduled_time (TIME) + start_date..end_date
-- -------------------------
CREATE TABLE medicine_routines (
  id             INT            NOT NULL AUTO_INCREMENT,
  user_id        INT UNSIGNED   NOT NULL,
  medicine_name  VARCHAR(255)   NOT NULL,
  dosage         VARCHAR(255)            NULL,
  time_of_day    VARCHAR(50)             NULL,
  notes          TEXT                     NULL,
  start_date     DATE                     NULL,
  end_date       DATE                     NULL,
  scheduled_date DATETIME                 NULL,
  created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notified       TINYINT(1)      NOT NULL DEFAULT 0,
  scheduled_time TIME                     NULL,
  mode           ENUM('one','recurring')  NOT NULL DEFAULT 'one',
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY ix_sched_date (scheduled_date),
  KEY ix_sched_time (scheduled_time),
  KEY ix_user_time (user_id, scheduled_time),
  CONSTRAINT fk_routines_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- MEDICINE TAKEN
-- -------------------------
CREATE TABLE medicine_taken (
  id         INT           NOT NULL AUTO_INCREMENT,
  routine_id INT UNSIGNED  NOT NULL,
  taken_date DATE          NOT NULL,
  taken_at   DATETIME               NULL,
  PRIMARY KEY (id),
  KEY routine_id (routine_id),
  CONSTRAINT fk_taken_routine
    FOREIGN KEY (routine_id) REFERENCES medicine_routines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- ACTIVITY LOG
-- -------------------------
CREATE TABLE activity_log (
  id         INT           NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED           NULL,      -- NULL за системни (cron) събития
  action     TEXT                    NOT NULL, -- напр. 'login', 'medicine_add', 'email_sent'
  timestamp  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_log_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;