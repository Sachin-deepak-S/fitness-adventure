# 🏋️‍♂️ Fitness Adventure 🎮🎉

**Fitness Adventure** is a gamified fitness tracker built with **PHP + MySQL**.  
It keeps you motivated with **quests, XP, streaks, leaderboards, and Party mode** – where you can team up with friends for shared goals.  

---

## 🚀 Features

- **🔐 Authentication**
  - Secure login & signup (`login.html`, `signup.html`, `login.php`, `signup.php`)
  - Session management (`logout.php`, `logout1.php`)
- **📊 Dashboard (`dashboard.php`)**
  - Personalized view after login  
  - Quick navigation to profile, quests, leaderboard, and parties  
- **👤 Profile (`profile.php`)**
  - Edit personal details (age, height, weight, goals, preferences)  
  - Avatar upload support (with file validation)  
  - Role field (locked for users)  
- **🎯 Quests (`quests.php`)**
  - Daily/weekly/monthly fitness challenges  
  - XP rewards + streaks for consistency  
  - Admin updates via `update_quest.php`, `update_monthly_quest.php`, `reset_quests.php`  
- **🏆 Leaderboard (`leaderboard.php`)**
  - Ranks users by XP and streak progress  
- **👥 Party System (`party.php`, `party_actions.php`)**
  - Create or join a party  
  - Shared progress tracking  
  - Team leaderboard coming soon 🚀  
- **📂 Database (`fitness_db.sql`)**
  - `users` → profile & login info  
  - `quests` → fitness quests  
  - `progress` → XP + streaks  
  - `parties` → party groups  
  - `party_members` → user-party mapping  

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS (gradient UI + animations)  
- **Backend:** PHP (sessions, CRUD, uploads)  
- **Database:** MySQL (XAMPP/WAMP compatible)  

---

## 📂 Project Structure

fitness-adventure/
│

├── bg.png
├── default.png
├── favicon.png
├── speed.png
├── warrior.png
└── yoga.png
│
├── fitness_db.sql
│
├── index.html
├── dashboard.php
├── profile.php
├── leaderboard.php
├── onboarding.php
├── welcome.php
│
├── login.html
├── login.php
├── signup.html
├── signup.php
├── logout.php
├── logout1.php
│
├── quests.php
├── reset_quests.php
├── update_quest.php
├── update_monthly_quest.php
│
├── party.php
└── party_actions.php

---

## 📸 Screenshots  

*(Add a `/screenshots` folder and place PNGs of your UI there)*  

- Login → ![Login](screenshots/login.png)  
- Dashboard → ![Dashboard](screenshots/dashboard.png)  
- Profile → ![Profile](screenshots/profile.png)  
- Quests → ![Quests](screenshots/quests.png)  
- Leaderboard → ![Leaderboard](screenshots/leaderboard.png)  
- Party → ![Party](screenshots/party.png)  
- Onboarding/Welcome → ![Onboarding](screenshots/onboarding.png)  

---

## ⚡ Installation & Setup

1. Clone the repo:
   ```bash
   git clone https://github.com/yourusername/fitness-adventure.git
   cd fitness-adventure
   cd fitness-adventure
2. Import the DB:

Create database fitness_db

Import fitness_db.sql

3. Update connection details in PHP files:
$servername = "localhost";
$username   = "root";
$password   = "1234"; 
$dbname     = "fitness_db";
$port       = 3307;

4. Run locally:

Move folder to htdocs/ (XAMPP)

Start Apache + MySQL

Visit: http://localhost:3307/fitness-adventure/index.html

🎯 Future Enhancements

Party chat & collaboration feed

Weekly party challenges

Global party leaderboard

Mobile-first responsive UI

📜 License

MIT License © 2025
Free to use, modify, and share.
