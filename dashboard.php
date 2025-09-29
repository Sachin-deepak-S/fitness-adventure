<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// ✅ Database config (move to config.php ideally)
$servername = "localhost";
$username   = "root";
$password   = "1234"; // ⚠️ Move to env/config for security
$dbname     = "fitness_db";
$port       = 3307;

// ✅ DB Connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Database connection failed. Please try again later.");
}

$user_id = $_SESSION['user_id'];
$today   = date("Y-m-d");

/* -------------------------------
   ✅ Daily Stat Reduction
-------------------------------- */
// Reduce stats once per day
$sql = "UPDATE user_progress 
        SET health = GREATEST(0, health - 5), 
            stamina = GREATEST(0, stamina - 5), 
            energy = GREATEST(0, energy - 5),
            last_updated = ?
        WHERE user_id = ? AND (last_updated IS NULL OR last_updated < ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $today, $user_id, $today);
$stmt->execute();

/* -------------------------------
   ✅ Fetch User + Progress
-------------------------------- */
$sql = "SELECT u.fullname, u.email, u.role, u.goal, u.preferences, u.avatar,
               p.xp, p.level, p.streak, p.health, p.stamina, p.energy
        FROM users u 
        JOIN user_progress p ON u.id = p.user_id 
        WHERE u.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User data not found.");
}

// ✅ Avatar path logic
if (!empty($user['avatar'])) {
    $avatarPath = htmlspecialchars($user['avatar']);
} else {
    switch ($user['role']) {
        case 'Warrior': $avatarPath = "avatar/warrior.png"; break;
        case 'Ninja':   $avatarPath = "avatar/speed.png"; break;
        case 'Monk':    $avatarPath = "avatar/yoga.png"; break;
        default:        $avatarPath = "avatar/default.png"; break;
    }
}

/* -------------------------------
   ✅ Monthly Quest Generator
-------------------------------- */
$monthStart = date("Y-m-01");
$sql = "SELECT * FROM monthly_quests WHERE user_id=? AND assigned_month=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $monthStart);
$stmt->execute();
$monthly = $stmt->get_result()->fetch_assoc();

if (!$monthly) {
    $goal = $user['goal'];
    $questText   = "Complete 20 Daily Quests this month";
    $targetCount = 20;
    $xpReward    = 1000;

    if ($goal == "Weight Loss") {
        $questText   = "Burn calories by completing 25 Daily Quests this month";
        $targetCount = 25;
        $xpReward    = 1200;
    } elseif ($goal == "Muscle Gain") {
        $questText   = "Finish 22 Strength-focused Daily Quests this month";
        $targetCount = 22;
        $xpReward    = 1300;
    } elseif ($goal == "Endurance") {
        $questText   = "Complete 26 Cardio-based Daily Quests this month";
        $targetCount = 26;
        $xpReward    = 1400;
    } elseif ($goal == "General Health") {
        $questText   = "Stay consistent with 20 Daily Quests this month";
        $targetCount = 20;
        $xpReward    = 1000;
    }

    $sql = "INSERT INTO monthly_quests (user_id, quest_text, target_count, xp_reward, assigned_month) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiis", $user_id, $questText, $targetCount, $xpReward, $monthStart);
    $stmt->execute();

    // Fetch updated quest
    $sql = "SELECT * FROM monthly_quests WHERE user_id=? AND assigned_month=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $monthStart);
    $stmt->execute();
    $monthly = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fitness Adventure - Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #00e0ff;
      --secondary-color: #ffd200;
      --accent-color: #ff4e50;
      --bg-gradient: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      --card-bg: rgba(255,255,255,0.08);
      --nav-bg: rgba(0,0,0,0.7);
      --shadow: 0 8px 32px rgba(0,0,0,0.3);
      --border-radius: 15px;
      --transition: all 0.3s ease;
    }

    body {
      font-family: 'Poppins', sans-serif;
      margin: 0; padding: 0;
      background: var(--bg-gradient);
      color: white; min-height: 100vh;
      display: flex; flex-direction: column;
      line-height: 1.6;
    }

    header {
      display: flex; justify-content: space-between; align-items: center;
      padding: 20px;
      background: var(--nav-bg);
      border-bottom: 2px solid var(--primary-color);
      backdrop-filter: blur(10px);
      transition: var(--transition);
    }

    header h1 {
      margin: 0;
      font-size: 2rem;
      color: var(--primary-color);
      text-shadow: 0 0 15px var(--primary-color);
      font-weight: 600;
    }

    .logout {
      padding: 10px 20px;
      background: var(--accent-color);
      border: none;
      border-radius: 25px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(255, 78, 80, 0.3);
    }

    .logout:hover {
      background: #e60023;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 78, 80, 0.4);
    }

    .dashboard {
      display: grid;
      grid-template-columns: 1fr 2fr 1fr;
      gap: 20px;
      padding: 20px;
      flex: 1;
    }

    .card {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      padding: 20px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.1);
      transition: var(--transition);
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.4);
    }

    .avatar-panel { text-align: center; }

    .avatar-panel img {
      width: 120px; height: 120px;
      border-radius: 50%;
      border: 3px solid var(--primary-color);
      margin-bottom: 15px;
      object-fit: cover;
      transition: var(--transition);
      box-shadow: 0 0 20px rgba(0, 224, 255, 0.3);
    }

    .avatar-panel img:hover {
      transform: scale(1.05);
      box-shadow: 0 0 30px rgba(0, 224, 255, 0.5);
    }

    .avatar-panel h3 {
      margin: 10px 0;
      font-size: 1.4rem;
      color: var(--secondary-color);
    }

    .stats { margin-top: 15px; text-align: left; }

    .bar {
      background: #444;
      border-radius: 10px;
      height: 14px;
      overflow: hidden;
      margin-bottom: 10px;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
    }

    .bar span {
      display: block;
      height: 100%;
      transition: width 1s ease-in-out;
    }

    .health { background: linear-gradient(90deg,#ff4e50,#f9d423); width: <?php echo intval($user['health']); ?>%; }
    .stamina{ background: linear-gradient(90deg,#00f260,#0575e6); width: <?php echo intval($user['stamina']); ?>%; }
    .energy { background: linear-gradient(90deg,#f7971e,#ffd200); width: <?php echo intval($user['energy']); ?>%; }

    .quest-preview h2 {
      color: var(--secondary-color);
      margin-top: 0;
      font-size: 1.5rem;
      text-shadow: 0 0 10px var(--secondary-color);
    }

    .quest-preview .progress-bar {
      background: #333;
      border-radius: 10px;
      height: 16px;
      overflow: hidden;
      margin: 15px 0;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
    }

    .quest-preview .progress-fill {
      background: linear-gradient(90deg,#00e0ff,#00ff9d);
      height: 100%;
      transition: width 1s ease-in-out;
    }

    .progress h2 {
      color: var(--primary-color);
      margin-top: 0;
      font-size: 1.5rem;
    }

    .progress-item {
      margin-bottom: 15px;
    }

    .progress-item p {
      margin: 5px 0;
      font-weight: 500;
    }

    nav {
      display: flex;
      justify-content: space-around;
      background: var(--nav-bg);
      padding: 15px;
      border-top: 2px solid var(--primary-color);
      backdrop-filter: blur(10px);
    }

    nav a {
      text-decoration: none;
      color: white;
      font-size: 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 10px;
      border-radius: 10px;
      transition: var(--transition);
    }

    nav a:hover {
      color: var(--primary-color);
      background: rgba(0, 224, 255, 0.1);
      transform: translateY(-3px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .dashboard {
        grid-template-columns: 1fr;
        gap: 15px;
        padding: 15px;
      }

      header {
        flex-direction: column;
        text-align: center;
        padding: 15px;
      }

      header h1 {
        font-size: 1.5rem;
        margin-bottom: 10px;
      }

      nav {
        flex-wrap: wrap;
        padding: 10px;
      }

      nav a {
        font-size: 0.9rem;
        padding: 8px;
      }

      .card {
        padding: 15px;
      }

      .avatar-panel img {
        width: 100px;
        height: 100px;
      }
    }

    @media (max-width: 480px) {
      header h1 {
        font-size: 1.3rem;
      }

      .quest-preview h2,
      .progress h2 {
        font-size: 1.3rem;
      }

      nav a {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>🏋️ Fitness Adventure Dashboard</h1>
    <form method="POST" action="logout.php">
      <button type="submit" class="logout">Logout</button>
    </form>
  </header>

  <main class="dashboard">

    <!-- Left Panel -->
    <div class="card avatar-panel">
      <img src="<?php echo $avatarPath; ?>" alt="User Avatar">
      <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
      <p><?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
      <div class="stats">
        <p>❤️ Health</p><div class="bar"><span class="health"></span></div>
        <p>⚡ Stamina</p><div class="bar"><span class="stamina"></span></div>
        <p>🔥 Energy</p><div class="bar"><span class="energy"></span></div>
      </div>
    </div>

    <!-- Middle Panel -->
    <div class="card quest-preview">
      <h2>🔥 Monthly Quest</h2>
      <?php if ($monthly) { 
          $progress = round(($monthly['progress_count'] / max(1,$monthly['target_count'])) * 100);
      ?>
          <p><?php echo htmlspecialchars($monthly['quest_text']); ?></p>
          <p>Progress: <?php echo intval($monthly['progress_count']); ?> / <?php echo intval($monthly['target_count']); ?></p>
          <div class="progress-bar">
              <div class="progress-fill" style="width:<?php echo $progress; ?>%"></div>
          </div>
          <p>Reward: +<?php echo intval($monthly['xp_reward']); ?> XP</p>
          <p>Status: <?php echo htmlspecialchars($monthly['status']); ?></p>
      <?php } else { ?>
          <p>No monthly quest assigned yet.</p>
      <?php } ?>
    </div>

    <!-- Right Panel -->
    <div class="card progress">
      <h2>📈 Progress</h2>
      <div class="progress-item">
        <p>XP: <?php echo intval($user['xp']); ?> / 1000</p>
        <div class="progress-bar">
          <div class="progress-fill" style="width:<?php echo min(100, round(($user['xp'] % 1000) / 10)); ?>%"></div>
        </div>
      </div>
      <div class="progress-item"><p>Level: <?php echo intval($user['level']); ?></p></div>
      <div class="progress-item"><p>🔥 Streak: <?php echo intval($user['streak']); ?> days</p></div>
    </div>

  </main>

  <nav>
    <a href="quests.php">🏋️ Quests</a>
    <a href="party.php">🧑‍🤝‍🧑 Party</a>
    <a href="leaderboard.php">🏅 Leaderboard</a>
    <a href="profile.php">⚙️ Profile</a>
  </nav>
</body>
</html>
