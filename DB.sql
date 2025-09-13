CREATE DATABASE IF NOT EXISTS medicine_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;
USE medicine_db;

-- -------------------------
-- USERS
-- -------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(100)      NOT NULL UNIQUE,
  email      VARCHAR(255)      NOT NULL,
  age        INT               NOT NULL,
  password   VARCHAR(255)      NOT NULL,
  created_at TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX ix_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- MEDICINE ROUTINES
--   mode = 'one'      -> use scheduled_date (DATETIME)
--   mode = 'recurring'-> use scheduled_time (TIME) + start_date..end_date
-- -------------------------
DROP TABLE IF EXISTS medicine_routines;
CREATE TABLE medicine_routines (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT            NOT NULL,
  mode            ENUM('one','recurring') NOT NULL,
  medicine_name   VARCHAR(100)   NOT NULL,

  -- one-time
  scheduled_date  DATETIME       NULL,

  -- recurring
  scheduled_time  TIME           NULL,
  start_date      DATE           NULL,
  end_date        DATE           NULL,

  created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_mr_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  -- helpful indexes for the queries used in get_schedule.php
  INDEX ix_user_mode (user_id, mode),
  INDEX ix_sched_date (scheduled_date),
  INDEX ix_sched_time (scheduled_time),
  INDEX ix_range (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- MEDICINE TAKEN (server-side record used in get_schedule.php filters)
--  The code checks:
--   - ONE-TIME: NOT EXISTS WHERE routine_id = mr.id AND taken_date = ?
--   - RECURRING: NOT EXISTS WHERE routine_id = mr.id AND taken_date = ? AND TIME(taken_at) = scheduled_time
-- -------------------------
DROP TABLE IF EXISTS medicine_taken;
CREATE TABLE medicine_taken (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  routine_id  INT            NOT NULL,
  taken_date  DATE           NOT NULL,
  taken_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  source      ENUM('web','device') NOT NULL DEFAULT 'device',

  CONSTRAINT fk_taken_routine
    FOREIGN KEY (routine_id) REFERENCES medicine_routines(id) ON DELETE CASCADE,

  -- Fast lookups in your NOT EXISTS subqueries
  INDEX ix_taken_routine_date (routine_id, taken_date),
  INDEX ix_taken_time (taken_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- (Optional) Demo data for quick smoke-test
-- -------------------------
-- INSERT INTO users (username, email, age, password)
-- VALUES ('demo', 'demo@example.com', 30, '$2y$10$hash');

-- -- One-time example: today at 08:00
-- INSERT INTO medicine_routines (user_id, mode, medicine_name, scheduled_date)
-- VALUES (1, 'one', 'Levothyroxin 50', CONCAT(CURDATE(),' 08:00:00'));

-- -- Recurring example: daily 13:00 for this week
-- INSERT INTO medicine_routines (user_id, mode, medicine_name, scheduled_time, start_date, end_date)
-- VALUES (1, 'recurring', 'Magnesium', '13:00:00', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 DAY));