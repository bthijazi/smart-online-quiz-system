CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE
);
CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE
);
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);
CREATE TABLE permissions_role (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,

    PRIMARY KEY (role_id, permission_id),

    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)
);
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,

    FOREIGN KEY (created_by) REFERENCES users(user_id)
);
CREATE TABLE connection_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    event_type VARCHAR(100),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE activity_logs (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE backups (
    backup_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    backup_name VARCHAR(150) NOT NULL UNIQUE,
    backup_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50),

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE quizzes (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    duration_minutes INT,
    pass_percentage FLOAT,

    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);
CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    points INT DEFAULT 1,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id)
);
CREATE TABLE quiz_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_id INT NOT NULL,
    score FLOAT,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME,
    is_flagged BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE question_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);
CREATE TABLE student_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT,
    is_correct BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id),
    FOREIGN KEY (selected_option_id) REFERENCES question_options(option_id)
);CREATE TABLE suspicious_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    alert_type VARCHAR(100),
    confidence_score FLOAT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id)
);
CREATE TABLE behavior_logs (
    behavior_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    tab_switches INT DEFAULT 0,
    mouse_leaves INT DEFAULT 0,
    gaze_deviation_count INT DEFAULT 0,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id)
);
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE
);
CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE
);
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);
CREATE TABLE permissions_role (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,

    PRIMARY KEY (role_id, permission_id),

    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)
);
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,

    FOREIGN KEY (created_by) REFERENCES users(user_id)
);
CREATE TABLE connection_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    event_type VARCHAR(100),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE activity_logs (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE backups (
    backup_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    backup_name VARCHAR(150) NOT NULL UNIQUE,
    backup_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50),

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE quizzes (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    duration_minutes INT,
    pass_percentage FLOAT,

    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);
CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    points INT DEFAULT 1,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id)
);
CREATE TABLE quiz_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_id INT NOT NULL,
    score FLOAT,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME,
    is_flagged BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE question_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);
CREATE TABLE student_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT,
    is_correct BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id),
    FOREIGN KEY (selected_option_id) REFERENCES question_options(option_id)
);CREATE TABLE suspicious_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    alert_type VARCHAR(100),
    confidence_score FLOAT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id)
);
CREATE TABLE behavior_logs (
    behavior_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    tab_switches INT DEFAULT 0,
    mouse_leaves INT DEFAULT 0,
    gaze_deviation_count INT DEFAULT 0,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id)
);
