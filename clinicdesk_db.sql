-- ============================================================
-- ClinicDesk — Database Setup
-- ============================================================

CREATE DATABASE IF NOT EXISTS clinicdesk_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE clinicdesk_db;

-- ── Table 1: users ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120)  NOT NULL,
    email      VARCHAR(180)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    phone      VARCHAR(20)   DEFAULT NULL,
    avatar     VARCHAR(255)  DEFAULT NULL,
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table 2: specializations ────────────────────────────────
CREATE TABLE IF NOT EXISTS specializations (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table 3: doctors ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS doctors (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNSIGNED NOT NULL UNIQUE,
    specialization_id  INT UNSIGNED NOT NULL,
    bio                TEXT         DEFAULT NULL,
    consultation_fee   DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    available_days     VARCHAR(50)  NOT NULL DEFAULT 'Sun,Mon,Tue,Wed,Thu',
    FOREIGN KEY (user_id)           REFERENCES users(id)           ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table 4: appointments ───────────────────────────────────
CREATE TABLE IF NOT EXISTS appointments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id   INT UNSIGNED NOT NULL,
    doctor_id    INT UNSIGNED NOT NULL,
    appt_date    DATE         NOT NULL,
    appt_time    TIME         NOT NULL,
    status       ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    reason       VARCHAR(255) DEFAULT NULL,
    doctor_notes TEXT         DEFAULT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY no_double_booking (doctor_id, appt_date, appt_time),
    FOREIGN KEY (patient_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table 5: prescriptions ──────────────────────────────────
CREATE TABLE IF NOT EXISTS prescriptions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL UNIQUE,
    diagnosis      TEXT         NOT NULL,
    medications    TEXT         NOT NULL,
    notes          TEXT         DEFAULT NULL,
    file_path      VARCHAR(255) DEFAULT NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin account  (password: Admin@1234)
INSERT IGNORE INTO users (name, email, password, role) VALUES
(
  'Admin',
  'admin@clinic.local',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);
-- ^ The hash above is bcrypt of "Admin@1234".
-- To generate your own:  echo password_hash('Admin@1234', PASSWORD_BCRYPT);


-- Specializations
INSERT IGNORE INTO specializations (name) VALUES
  ('General Practice'),
  ('Cardiology'),
  ('Dermatology'),
  ('Pediatrics'),
  ('Orthopedics'),
  ('Neurology'),
  ('Ophthalmology'),
  ('ENT'),
  ('Psychiatry');


-- Sample doctor user  (password: Doctor@1234)
INSERT IGNORE INTO users (name, email, password, role, phone) VALUES
(
  'Dr. Sarah Ahmed',
  'sarah@clinic.local',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'doctor',
  '+970-59-000-0001'
);

-- Corresponding doctor record (links to Dr. Sarah, Cardiology)
INSERT IGNORE INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days)
SELECT u.id, s.id,
       'Experienced cardiologist with 10+ years in interventional cardiology.',
       75.00,
       'Sun,Mon,Tue,Wed,Thu'
FROM   users u
JOIN   specializations s ON s.name = 'Cardiology'
WHERE  u.email = 'sarah@clinic.local'
LIMIT  1;


-- Sample patient  (password: Patient@1234)
INSERT IGNORE INTO users (name, email, password, role, phone) VALUES
(
  'Mohammed Al-Sayed',
  'patient@clinic.local',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'patient',
  '+970-59-000-0002'
);
-- ^ password hash is the same string for demo purposes.
-- Real passwords MUST differ per account.
