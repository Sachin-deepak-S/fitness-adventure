<div align="center">

# 🏋️‍♂️ Fitness Adventure

**A gamified fitness tracker that turns your workouts into an RPG adventure**

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

*Level up your fitness with quests, XP, streaks, and party challenges — because working out alone is boring.*

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Screenshots](#-screenshots)
- [Project Structure](#-project-structure)
- [Installation](#-installation--setup)
- [Database Schema](#-database-schema)
- [Future Enhancements](#-future-enhancements)
- [License](#-license)

---

## 🌟 Overview

**Fitness Adventure** is a PHP + MySQL web application that gamifies your fitness journey. Complete daily and weekly quests to earn XP, maintain streaks for consistency bonuses, and team up with friends in **Party Mode** for shared goals and friendly competition.

---

## 🚀 Features

### 🔐 Authentication
- Secure user login and session management
- Multi-step signup with profile onboarding
- Avatar selection and upload support

### 📊 Dashboard
- Personalized hub with quick stats at a glance
- One-click navigation to quests, party, leaderboard, and profile

### 👤 Profile Management
- Edit personal details — age, height, weight, fitness goals
- Upload or select a custom avatar
- Role display (admin / user)

### 🎯 Quests
- Daily and weekly fitness challenges
- Mark completions and earn XP rewards
- Auto-reset system for recurring quests

### 🎮 Gamification
- **XP System** — earn points for every completed activity
- **Streak Tracking** — stay consistent and build momentum
- **Leaderboard** — compete with other users globally

### 👥 Party System
- Create or join a party with friends
- Shared quest progress and team streaks
- Party leaderboard for group competition

---

## 📸 Screenshots

### 🔑 Authentication

| Login | Signup (Step 1) | Signup (Step 2) |
|-------|----------------|----------------|
| ![Login](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/login.png) | ![Signup 1](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/signup1.png) | ![Signup 2](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/signup2.png) |

| Signup (Step 3) | Avatar Select | Welcome |
|----------------|--------------|---------|
| ![Signup 3](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/signup3.png) | ![Avatar](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/avatarselect.png) | ![Welcome](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/welcome.png) |

---

### 🏠 Dashboard & Profile

| Dashboard | Profile — View | Profile — Edit |
|-----------|---------------|----------------|
| ![Dashboard](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/dashboard.png) | ![Profile 1](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/profile1.png) | ![Profile 2](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/profile2.png) |

---

### 🎯 Quests & Party

| Quests | Party System |
|--------|-------------|
| ![Quests](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/quest.png) | ![Party](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/party.png) |

---

### 🏆 Leaderboard

| Top Rankings | Extended View |
|-------------|--------------|
| ![Leaderboard 1](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/leaderboard1.png) | ![Leaderboard 2](https://raw.githubusercontent.com/Sachin-deepak-S/fitness-adventure/main/Screenshot/leaderboard2.png) |

---

## 📂 Project Structure
```
fitness-adventure/
│
├── 📸 Screenshot/               # UI preview images
│
├── 🗄️  fitness_db.sql           # Database schema & seed data
│
├── 🌐 index.html                # Landing page
├── 👋 welcome.php               # Post-signup welcome screen
├── 🚀 onboarding.php            # New user onboarding flow
│
├── 🔑 login.html                # Login form
├── 🔑 login.php                 # Login handler
├── 📝 signup.html               # Signup form
├── 📝 signup.php                # Signup handler
├── 🚪 logout.php                # Session logout
├── 🚪 logout1.php               # Alternative logout
│
├── 📊 dashboard.php             # Main user dashboard
├── 👤 profile.php               # Profile view & edit
├── 🏆 leaderboard.php           # Global XP leaderboard
│
├── 🎯 quests.php                # Quest listing & completion
├── ♻️  reset_quests.php          # Daily/weekly quest reset
├── 🔄 update_quest.php          # Quest progress updater
├── 🔄 update_monthly_quest.php  # Monthly quest handler
│
├── 👥 party.php                 # Party view & management
└── ⚡ party_actions.php         # Party CRUD actions
```

---

## 🗄️ Database Schema

| Table | Description |
|-------|-------------|
| `users` | Stores login credentials, profile info, and avatar |
| `quests` | Fitness quest definitions (daily/weekly/monthly) |
| `progress` | Tracks XP, streaks, and goal completion per user |
| `parties` | Party groups and metadata |
| `party_members` | Maps users to their party |

---

## ⚡ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP + MySQL stack)
- PHP 7.4+
- MySQL 5.7+

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/Sachin-deepak-S/fitness-adventure.git
```

**2. Move to your server root**
```bash
# XAMPP (Windows)
mv fitness-adventure/ C:/xampp/htdocs/

# XAMPP (macOS)
mv fitness-adventure/ /Applications/XAMPP/htdocs/
```

**3. Import the database**
- Open **phpMyAdmin** → Create database `fitness_db`
- Import `fitness_db.sql`

**4. Configure database connection**

Update the connection settings in your PHP files:
```php
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "fitness_db";
$port       = 3307;
```

**5. Launch the app**
- Start **Apache** and **MySQL** in XAMPP Control Panel
- Visit: [http://localhost/fitness-adventure/index.html](http://localhost/fitness-adventure/index.html)

> ⚠️ **Note:** The default MySQL port is `3306`. Update `$port` if yours differs.

---

## 🔭 Future Enhancements

- [ ] 💬 Party chat & collaboration feed
- [ ] 🏆 Party-wide challenges (e.g., *"Collect 500 XP as a team this week"*)
- [ ] 🌍 Global party leaderboard
- [ ] 📱 Mobile-first UI redesign
- [ ] 🔔 Push notifications for quest reminders
- [ ] 📈 Progress charts and fitness analytics

---

## 📜 License
```
MIT License © 2025

Free to use, modify, and distribute.
```

---

<div align="center">

Made with ❤️ and way too many skipped leg days.

⭐ **Star this repo if it helped you!** ⭐

</div>