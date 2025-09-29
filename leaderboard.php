<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username   = "root";
$password   = "1234"; // update if needed
$dbname     = "fitness_db";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Default leaderboard type
$type = isset($_GET['type']) && $_GET['type'] === "streak" ? "streak" : "global";

// Fetch top 10 leaderboard
if ($type === "streak") {
    $sql = "SELECT u.id, u.fullname, u.role, u.avatar, p.level, p.xp, p.streak
            FROM users u 
            JOIN user_progress p ON u.id = p.user_id
            ORDER BY p.streak DESC, p.xp DESC 
            LIMIT 10";
} else {
    $sql = "SELECT u.id, u.fullname, u.role, u.avatar, p.level, p.xp, p.streak
            FROM users u 
            JOIN user_progress p ON u.id = p.user_id
            ORDER BY p.xp DESC, p.streak DESC 
            LIMIT 10";
}
$result = $conn->query($sql);

// Fetch logged-in user rank
if ($type === "streak") {
    $rank_sql = "SELECT COUNT(*)+1 AS rank FROM users u 
                 JOIN user_progress p ON u.id = p.user_id
                 WHERE p.streak > (SELECT streak FROM user_progress WHERE user_id=?)";
} else {
    $rank_sql = "SELECT COUNT(*)+1 AS rank FROM users u 
                 JOIN user_progress p ON u.id = p.user_id
                 WHERE p.xp > (SELECT xp FROM user_progress WHERE user_id=?)";
}
$stmt = $conn->prepare($rank_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rank_result = $stmt->get_result();
$user_rank = $rank_result->fetch_assoc()['rank'] ?? "N/A";

// Fetch logged-in user data
$user_sql = "SELECT u.fullname, u.role, u.avatar, p.level, p.xp, p.streak 
             FROM users u 
             JOIN user_progress p ON u.id = p.user_id 
             WHERE u.id=?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Leaderboard</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: white;
      margin: 0;
      padding: 0;
    }
    header {
      text-align: center;
      padding: 20px;
      background: rgba(0,0,0,0.6);
      border-bottom: 2px solid #00e0ff;
      position: relative;
    }
    header h1 { margin: 0; font-size: 2rem; color: #00e0ff; }
    .back-btn {
      position: absolute;
      left: 20px;
      top: 20px;
      padding: 8px 15px;
      background: #00e0ff;
      color: black;
      font-weight: bold;
      text-decoration: none;
      border-radius: 8px;
      transition: 0.3s;
    }
    .back-btn:hover { background: #ffcc00; }
    .filters {
      text-align: center;
      margin: 15px 0;
    }
    .filters a {
      margin: 0 10px;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 8px;
      background: #00e0ff;
      color: black;
      font-weight: bold;
    }
    .filters a.active {
      background: #ffcc00;
      color: black;
    }
    table {
      width: 90%;
      margin: 20px auto;
      border-collapse: collapse;
      background: rgba(255,255,255,0.08);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,224,255,0.3);
    }
    th, td { padding: 12px; text-align: center; }
    th {
      background: rgba(0,0,0,0.5);
      color: #00e0ff;
    }
    tr:nth-child(even) { background: rgba(255,255,255,0.05); }
    tr.highlight { background: rgba(0,224,255,0.2); font-weight: bold; }
    .medal { font-size: 1.5rem; }
    img.avatar {
      width: 40px; height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #00e0ff;
    }
    .badge {
      font-size: 0.8rem;
      padding: 3px 6px;
      border-radius: 6px;
      margin-left: 5px;
    }
    .streak-master { background: #ff5722; color: white; }
  </style>
</head>
<body>
  <header>
    <a href="dashboard.php" class="back-btn">⬅ Back</a>
    <h1>🏆 Leaderboard</h1>
  </header>

  <div class="filters">
    <a href="?type=global" class="<?= $type=='global' ? 'active' : '' ?>">🌍 Global</a>
    <a href="?type=streak" class="<?= $type=='streak' ? 'active' : '' ?>">🔥 Streak</a>
  </div>

  <table>
    <tr>
      <th>Rank</th>
      <th>Avatar</th>
      <th>Name</th>
      <th>Level</th>
      <th>XP</th>
      <th>Streak</th>
    </tr>
    <?php
    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        $avatarPath = !empty($row['avatar']) ? htmlspecialchars($row['avatar']) : "avatar/". strtolower($row['role']).".png";

        // 🥇🥈🥉 medals
        $medal = ($rank == 1) ? "🥇" : (($rank == 2) ? "🥈" : (($rank == 3) ? "🥉" : ""));

        // 🔥 streak badge
        $streakBadge = ($row['streak'] >= 30) ? "<span class='badge streak-master'>🔥 Streak Master</span>" : "";

        echo "<tr>
                <td>{$medal} #{$rank}</td>
                <td><img class='avatar' src='{$avatarPath}'></td>
                <td>".htmlspecialchars($row['fullname'])."</td>
                <td>".intval($row['level'])."</td>
                <td>".intval($row['xp'])."</td>
                <td>".intval($row['streak'])." days {$streakBadge}</td>
              </tr>";
        $rank++;
    }
    ?>
  </table>

  <!-- Highlight Logged-in User -->
  <?php if ($user_data): ?>
  <h2 style="text-align:center; margin-top:30px;">🙋 Your Rank</h2>
  <table>
    <tr class="highlight">
      <td>#<?php echo htmlspecialchars($user_rank); ?></td>
      <td>
        <img class="avatar" src="<?php echo !empty($user_data['avatar']) ? htmlspecialchars($user_data['avatar']) : 'avatar/'. strtolower($user_data['role']).'.png'; ?>">
      </td>
      <td><?php echo htmlspecialchars($user_data['fullname']); ?></td>
      <td><?php echo intval($user_data['level']); ?></td>
      <td><?php echo intval($user_data['xp']); ?></td>
      <td>
        <?php echo intval($user_data['streak']); ?> days 
        <?php if ($user_data['streak'] >= 30) echo "<span class='badge streak-master'>🔥 Streak Master</span>"; ?>
      </td>
    </tr>
  </table>
  <?php endif; ?>
</body>
</html>
