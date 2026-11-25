-- Smart Online Quiz System Backup
-- Date: 2026-11-24 14:03:01

-- Table: roles (3 rows)
INSERT INTO roles (role_id, role_name) VALUES ('1', 'admin');
INSERT INTO roles (role_id, role_name) VALUES ('2', 'instructor');
INSERT INTO roles (role_id, role_name) VALUES ('3', 'student');

-- Table: users (3 rows)
INSERT INTO users (user_id, role_id, username, email, password_hash, created_at) VALUES ('1', '1', 'Administrator', 'admin@smartquiz.com', '$2y$10$AqpL4FXfVggcaLyNyNe5gOZPKdb0eHfwupDWRdJzNc1VuS6piuYy6', '2026-11-24 13:08:29');
INSERT INTO users (user_id, role_id, username, email, password_hash, created_at) VALUES ('2', '2', 'Instructor', 'instructor@smartquiz.com', '$2y$10$o3wazanbC1.ru/da1llW6.9w0vtin5CQBdyRUY3uziJ0vUO9vuXhC', '2026-11-24 13:08:29');
INSERT INTO users (user_id, role_id, username, email, password_hash, created_at) VALUES ('3', '3', 'Student', 'student@smartquiz.com', '$2y$10$z2IpBhBfrt99gVRzLXFn0.I/I/JaH2o8oaNOJnfbmTi652RtUoHIu', '2026-11-24 13:08:29');

-- Table: courses (4 rows)
INSERT INTO courses (course_id, course_name, created_by, created_at) VALUES ('1', 'Introduction to Programming', '2', '2026-11-24 13:08:29');
INSERT INTO courses (course_id, course_name, created_by, created_at) VALUES ('2', 'Database Management Systems', '2', '2026-11-24 13:08:29');
INSERT INTO courses (course_id, course_name, created_by, created_at) VALUES ('3', 'Artificial Intelligence', '2', '2026-11-24 13:08:29');
INSERT INTO courses (course_id, course_name, created_by, created_at) VALUES ('4', 'Web Engineering', '2', '2026-11-24 13:08:29');

-- Table: quizzes (0 rows)

-- Table: questions (0 rows)

-- Table: question_options (0 rows)

-- Table: quiz_questions (0 rows)

-- Table: quiz_attempts (0 rows)

-- Table: student_answers (0 rows)

