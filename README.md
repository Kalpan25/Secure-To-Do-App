# Secure-To-Do-App
# 🔒 Secure To‑Do List Web App

A simple but security-focused **PHP + SQLite** to‑do list application that demonstrates core **web security principles**: secure authentication, CSRF protection, XSS prevention, and SQL injection defense. Built as a final project for a web security course.

---

## ✨ Features

- 🧾 User registration and login (with **strong password policy**)
- ✅ Create, complete, and delete personal to‑do items
- 👑 Admin dashboard to:
  - View all users
  - View all todos
  - See system stats (total users/todos/completed/pending)
  - Delete non-admin users and their todos
- 🎨 Modern UI with:
  - Video background (`1.mp4`)
  - Glassmorphism auth cards
  - Smooth animations and hover effects
  - Password strength indicator
  - Button loading spinners
  - Tooltips on key actions

---

## 🛡️ Security Highlights

This project is intentionally designed to show **defenses against common attacks**:

- **Authentication**
  - Passwords hashed with **bcrypt** via `password_hash()` (cost = 12) [web:18]
  - Strong password rules (min length + upper/lower/number)
  - Session-based login tracking
  - HttpOnly cookies enabled

- **SQL Injection (SQLi)**
  - All DB access via **PDO prepared statements**
  - `ATTR_EMULATE_PREPARES = false` to enforce real prepared statements [web:8]

- **Cross-Site Request Forgery (CSRF)**
  - Per-session CSRF token generated with `random_bytes()`
  - Hidden `<input>` with token on **all** state-changing POST forms
  - `hash_equals()` used for timing-safe comparison [web:11]

- **Cross-Site Scripting (XSS)**
  - All user-generated content escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` before rendering [web:1]

- **Authorization**
  - Every todo query checks `user_id` so users can only modify **their own** data
  - Admin-only routes protected with `Database::isAdmin($userId)`

A full **Security Design & Testing Report (PDF)** is included in the repo, documenting attack scenarios and tests (SQLi, CSRF, XSS, auth bypass, authorization checks).

---

## 🧱 Tech Stack

- **Frontend:** HTML, CSS, vanilla JS
- **Backend:** PHP (no framework)
- **Database:** SQLite (via PDO)
- **Server:** PHP built-in server (`php -S`)

---

## 🚀 Getting Started

### 1. Prerequisites

- PHP 7.4+ with SQLite extension enabled [web:12]
- A browser (Chrome/Firefox/etc.)

### 2. Clone the Repo
git clone https://github.com/<your-username>/<your-repo>.git
cd <your-repo>


### 3. Initialize Database & Admin User

This script:
- Deletes any old `todos.db`
- Creates tables with `is_admin` column
- Creates an **admin** account

php setup-admin.php

text

Admin credentials:

- **Username:** `admin`  
- **Password:** `Admin123!`

### 4. Run the App

php -S localhost:8000

text

Open your browser at: `http://localhost:8000`

---

## 👤 User Flows

### Regular User

1. Go to `/register.php` and create an account
2. Log in at `/index.php`
3. Add todos, toggle complete, delete todos

### Admin

1. Log in as **admin**
2. Click **“Admin Panel”** button on dashboard
3. From the admin dashboard you can:
   - View all users + roles
   - View all todos with owner names
   - Delete non-admin users and their todos
   - See overall stats (cards at top)

---

## 📂 Project Structure

---

├── index.php # Login
├── register.php # Registration
├── dashboard.php # User to‑do dashboard
├── admin.php # Admin dashboard
├── logout.php # Session/logout handler
├── config.php # Session & DB config
├── security.php # CSRF, hashing, escaping, auth helpers
├── database.php # PDO connection + all DB queries
├── setup-admin.php # Initializes DB & creates admin account
├── todos.db # SQLite database (auto-generated)
├── styles.css # UI, animations, responsive design
└── docs/
└── Security-Report.pdf # Security design & testing report (optional path)



---

## 🔬 Security Testing (High Level)

The report and code demonstrate tests for:

- **SQL Injection**
  - Tried payloads like `admin'--`, `' OR '1'='1`, and `'; DROP TABLE users; --`
  - All blocked by prepared statements

- **CSRF**
  - External HTML pages auto-submitting POST forms without CSRF tokens
  - All blocked with “Security validation failed” responses

- **XSS**
  - Script tags, event handlers, SVG-based XSS payloads in todo titles/descriptions
  - All rendered as text due to output escaping

- **Auth & Authorization**
  - Brute-force simulation (common passwords)
  - Cross-user todo deletion attempts by tampering `todo_id`
  - Admin route access from non-admin users
  - All correctly denied

Details (including code snippets and payloads) are in the PDF report.

---

## 🧪 Demo Scenarios for Presentations

- **Show login + secure session**
- **Create a todo, toggle complete, delete it**
- **Attempt a SQL injection login → fails**
- **Try to inject `<script>alert('XSS')</script>` as a title → shows as text**
- **Visit malicious CSRF page (test HTML file) → action blocked**
- **Log in as admin and walk through the admin dashboard**

---

## 📎 Acknowledgements / References

- OWASP Cheat Sheets (Password Storage, XSS, CSRF) [web:18][web:1][web:11]  
- PDO & prepared statement best practices [web:8][web:12]  
- General README best practices from GitHub docs and community examples [web:29][web:33][web:36]  

---

## 📜 License



MIT License
Copyright (c) 2024 ...


---

## 🙋‍♂️ Author

- **Name:** Kalpan Patel  
