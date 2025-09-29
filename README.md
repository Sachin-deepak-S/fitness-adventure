# Fitness Adventure 🏋️‍♂️🎮🎉

**Fitness Adventure** is a gamified fitness tracker built using **PHP + MySQL**.  
It keeps you motivated with **quests, XP, streaks, progress tracking, and now Party mode** – where you can team up with friends for shared goals.  

---

## 🚀 Features

- **Authentication**
  - User login & session management (`login.html`, `register.html`, `logout.php`)
- **Dashboard (`dashboard.php`)**
  - Personalized user view after login
  - Navigation to profile, quests, and parties
- **Profile (`profile.php`)**
  - Edit personal details (age, height, weight, goals, preferences)
  - Change avatar (with upload support)
  - View locked role field (admin/user)
- **Quests (`quests.php`)**
  - Daily/weekly quests for fitness activities
  - Track completion & earn XP
- **Gamification**
  - XP system and target goals
  - Streak tracking for consistency
- **Party System (`party.php`)**
  - Create or join a party with friends
  - Shared quest progress & streaks
  - Team leaderboard for fun competition
- **Database (`fitness_db`)**
  - `users` → stores profile & login info  
  - `quests` → fitness quests list  
  - `progress` → XP, streaks, goal tracking  
  - `parties` → stores party groups  
  - `party_members` → links users to their party  

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS (modern UI with gradients, animations)  
- **Backend:** PHP (session handling, CRUD, file uploads)  
- **Database:** MySQL (MariaDB, works with XAMPP/WAMP)  

---

## 📂 Project Structure



---

## ⚡ Installation & Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/fitness-adventure.git
   cd fitness-adventure
2. Import database:

Create a database fitness_db

Import the provided fitness_db.sql

3. Update DB connection settings in PHP files:
$servername = "localhost";
$username   = "root";
$password   = "1234"; 
$dbname     = "fitness_db";
$port       = 3307;

4. Run on local server:

Place folder inside htdocs/ (XAMPP)

Start Apache + MySQL in XAMPP

Visit: http://localhost:3307/fitness-adventure/login.html

🎯 Future Enhancements

Party chat & collaboration feed

Party challenges (e.g., “Collect 500 XP as a team this week”)

Global leaderboard of parties

Mobile-first UI redesign

📜 License

MIT License © 2025
Free to use, modify, and share.
