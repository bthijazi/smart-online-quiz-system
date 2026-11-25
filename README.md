# Smart Online Quiz System (SOQS)

An intelligent, bilingual (**English / Arabic RTL**) web-based examination and assessment platform built with **Laravel**, **MySQL**, **Vanilla JavaScript**, and a **Python Machine Learning (Decision Tree)** proctoring microservice.

---

## Key Features

### 1. Role-Based Portals
* **Admin Dashboard:** Manage students and instructors, monitor system-wide statistics, review all exam attempts with AI proctoring flags, and generate real SQL database backups.
* **Instructor Portal:**
  * Manage courses, lectures, and a categorized **Question Bank** (MCQ, True/False, Essay) with full edit/delete support.
  * **3-Step Quiz Wizard:** Create assessments using three flexible exam modes:
    1. **Fixed Questions:** Manual question order.
    2. **Random Subset from Pool:** Draws a randomized subset of questions per student.
    3. **Sectioned by Lecture:** Draws a specific number of random questions and marks from each lecture.
  * Control **Question Type Display Order** (e.g., True/False → MCQ → Essay) while keeping questions randomized within each type.
  * Configure **Questions Per Page**, **Allow/Disable Backtracking**, **Result Release Policy** (Immediate, After Deadline, Manual), and **Answer Visibility** (Show/Hide correct answers).
  * Grade essay questions manually with automatic total score recalculation.
* **Student Portal:**
  * View scheduled exams with a **live countdown timer** for upcoming assessments.
  * Single-attempt enforcement (`One Attempt Only`) and automatic submission upon duration expiry or exam deadline (`Available Until`).
  * Anti-cheat protections during exams (disabled Copy/Cut/Paste, Right-Click, and keyboard shortcuts).

### 2. AI Proctoring & Behavioral Monitoring
* Tracks real-time student behavior during exams: **Tab/Window Switches**, **Cursor Exit from Exam Area**, **Unauthorized Copy/Paste Attempts**, and **Completion Duration**.
* Evaluates behavioral metrics via a **Python Decision Tree Classifier** (`app/ai/ai_service.py`) and flags suspicious attempts with detailed reasons in both Instructor and Admin dashboards.

### 3. Bilingual & Responsive UI
* Instant switching between **English (LTR)** and **Arabic (RTL)** with full layout mirroring and modern **Cairo** typography.

---

## 🛠️ Tech Stack
* **Backend:** PHP 8+, Laravel Framework, Laravel Sanctum (API Authentication)
* **Database:** MySQL (`soqs_proj_db`)
* **AI Service:** Python 3, Flask, Scikit-Learn (`DecisionTreeClassifier`)
* **Frontend:** HTML5, CSS3 (Responsive + RTL), Vanilla JavaScript (Fetch API)

---

## Installation & Setup Guide

### 1. Clone the Repository
```bash
git clone -b "backend&ai" https://github.com
cd smart-online-quiz-system
```

### 2. Install PHP Dependencies & Configure Environment
```bash
composer install
cp .env.example .env
php artisan key:generate
```
Update your `.env` file with your local MySQL credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=soqs_proj_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Database Migrations & Seeders
```bash
php artisan migrate --seed
```

### 4. Start the Laravel Server
```bash
php artisan serve
```
The application will be available at: **`http://127.0.0.1:8000`**

### 5. Start the Python AI Proctoring Service
In a separate terminal:
```bash
pip install flask scikit-learn numpy
python app/ai/ai_service.py
```
The AI service runs on: **`http://127.0.0.1:5000`**

---

## Demo Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@smartquiz.com` | `Admin123` |
| **Instructor** | `instructor@smartquiz.com` | `Instructor123` |
| **Student** | `student@smartquiz.com` | `Student123` |
