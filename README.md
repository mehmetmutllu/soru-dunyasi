# 🌍 Soru Dünyası (Question World)

![Project Status](https://img.shields.io/badge/Status-Active-success)
![License](https://img.shields.io/badge/License-MIT-blue)
![Platform](https://img.shields.io/badge/Platform-Web-orange)

> **A comprehensive, gamified quiz platform designed for exam preparation.**

**Soru Dünyası**, primarily targeting Turkish exams like **YKS** and **LGS**, provides an interactive environment for users to test their knowledge, track progress, and compete with others.

---

## 🚀 Features

### 👤 User Experience
* **Secure Authentication:** Robust registration and login system with session management.
* **Personalized Dashboard:** A dynamic hub showing success rates, tests solved, daily streaks, and rank.
* **Interactive Quizzes:** A modern, single-page quiz interface powered by asynchronous API calls.
* **Detailed Analysis:** Review every question after the quiz to learn from mistakes.

### 🏆 Gamification
* **Leaderboard:** "Champions League" style ranking with a visual podium for the top 3 users.
* **Daily Streak:** Tracks consecutive activity to encourage daily study.
* **Scoring System:** Points are awarded for correct answers to drive competition.

### 📚 Education Library
* **Structured Content:** Organized hierarchy: `Exam` -> `Lesson` -> `Topic`.
* **Study Materials:** Topics include articles and specific quizzes for focused learning.

### 🛠️ Admin Panel
* **Full Control:** Create, update, and delete quizzes and questions effortlessly.
* **System Stats:** View total users, quizzes, and engagement metrics at a glance.

---

## 💻 Tech Stack

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white) | Core application logic |
| **Database** | ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white) | Data storage and management |
| **Frontend** | ![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat&logo=tailwind-css&logoColor=white) | Modern and responsive styling |
| **Scripting** | ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black) | Async operations & interactivity |

---

## 📂 Project Structure

The project follows a modular structure to separate public views, backend logic, and admin functionalities.

```text
/
├── admin/            # 🔒 Admin panel pages and logic
├── api/              # ⚡ JSON endpoints for async operations
├── assets/           # 🎨 Frontend assets (JS, CSS, Images)
├── includes/         # 🧩 Reusable components (DB conn, header, footer)
├── index.php         # 🏠 Dashboard & Landing page
├── quiz.php          # 📝 Main quiz interface
├── leaderboard.php   # 🏆 User rankings
├── library.php       # 📚 Education content browser
├── profile.php       # 👤 User history and settings
└── ...               # Core logic files
