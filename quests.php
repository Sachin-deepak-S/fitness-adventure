<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "fitness_db";
$port       = 3307;
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];

/* -------------------------------
   Fetch User + Progress Data
-------------------------------- */
$sql = "SELECT u.id, u.role, u.activity_level, u.goal, u.preferences,
               p.level, p.streak, p.health, p.stamina, p.energy
        FROM users u
        JOIN user_progress p ON u.id = p.user_id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die("User not found!");

$today = date("Y-m-d");

/* -------------------------------
   Count Today’s Quests
-------------------------------- */
$sql = "SELECT COUNT(*) as cnt FROM quests WHERE user_id=? AND DATE(assigned_at)=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$quest_count = $stmt->get_result()->fetch_assoc()['cnt'];

/* -------------------------------
   Quest Pools (Role + Activity + Goal + Preference)
-------------------------------- */
function getQuestPool($role, $activity, $goal, $preference) {
    $multiplier = ($activity == "Beginner") ? 1 : (($activity == "Intermediate") ? 2 : 3);
    $xp = ($activity == "Beginner") ? 50 : (($activity == "Intermediate") ? 100 : 150);

    $quests = [];

    // Base by Role
    if ($role == "Warrior") {
        $quests = [
            ["Do " . (10*$multiplier) . " push-ups", "Strength", $xp],
            ["Do " . (15*$multiplier) . " squats", "Strength", $xp],
            ["Run " . (1*$multiplier) . " km", "Cardio", $xp],
        ];
    } elseif ($role == "Ninja") {
        $quests = [
            ["Do " . (20*$multiplier) . " burpees", "Cardio", $xp],
            ["Sprint " . (200*$multiplier) . " m", "Agility", $xp],
            ["Do " . (30*$multiplier) . " mountain climbers", "Cardio", $xp],
        ];
    } elseif ($role == "Monk") {
        $quests = [
            ["Yoga for " . (10*$multiplier) . " min", "Flexibility", $xp],
            ["Meditate for " . (5*$multiplier) . " min", "Mindfulness", $xp],
            ["Deep breathing for " . (3*$multiplier) . " min", "Mindfulness", $xp],
        ];
    }

    // Modify by Goal
    if ($goal == "Weight Loss") {
        $quests[] = ["Jog for " . (2*$multiplier) . " km", "Cardio", $xp];
        $quests[] = ["Do " . (50*$multiplier) . " jumping jacks", "Cardio", $xp];
    } elseif ($goal == "Muscle Gain") {
        $quests[] = ["Do " . (12*$multiplier) . " dumbbell curls", "Strength", $xp];
        $quests[] = ["Do " . (15*$multiplier) . " bench presses", "Strength", $xp];
    } elseif ($goal == "General Health") {
        $quests[] = ["Walk for " . (2*$multiplier) . " km", "Cardio", $xp];
        $quests[] = ["Stretch for " . (5*$multiplier) . " min", "Flexibility", $xp];
    } elseif ($goal == "Endurance") {
        $quests[] = ["Cycle for " . (5*$multiplier) . " km", "Cardio", $xp];
        $quests[] = ["Do " . (3*$multiplier) . " min plank hold", "Strength", $xp];
    }

    // Adjust with Preference
    if ($preference == "Gym Workout") {
        $quests[] = ["Lift weights for " . (20*$multiplier) . " reps", "Strength", $xp];
    } elseif ($preference == "Home Workout") {
        $quests[] = ["Do " . (20*$multiplier) . " bodyweight squats", "Strength", $xp];
    } elseif ($preference == "Yoga") {
        $quests[] = ["Practice Sun Salutation for " . (5*$multiplier) . " rounds", "Flexibility", $xp];
    } elseif ($preference == "Others") {
        $quests[] = ["Take stairs instead of elevator", "General", $xp];
    }

    return $quests;
}

/* -------------------------------
   Assign Quests if Missing
-------------------------------- */
if ($quest_count < 3) {
    $pool = getQuestPool($user['role'], $user['activity_level'], $user['goal'], $user['preferences']);
    shuffle($pool);

    for ($i = $quest_count; $i < 3; $i++) {
        $q = $pool[$i];
        $plan_category = $user['goal'] . " / " . $user['preferences'];

        $sql = "INSERT INTO quests (user_id, quest_text, category, xp_reward, plan_category, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issis", $user['id'], $q[0], $q[1], $q[2], $plan_category);
        $stmt->execute();
    }
}

/* -------------------------------
   Fetch Today’s Quests
-------------------------------- */
$sql = "SELECT * FROM quests WHERE user_id=? AND DATE(assigned_at)=? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$quests_today = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>🎯 Today's Quests</title>
  <style>
    body { font-family: Poppins, sans-serif; background:#101820; color:white; text-align:center; }
    h1 { color:#00e0ff; }
    table { margin:20px auto; width:95%; border-collapse:collapse; background:rgba(255,255,255,0.05); border-radius:12px; overflow:hidden; }
    th, td { padding:12px; border-bottom:1px solid rgba(255,255,255,0.2); }
    th { background:#00e0ff; color:black; }
    .btn { padding:6px 12px; border-radius:6px; border:none; cursor:pointer; }
    .complete { background:#4caf50; color:white; }
    .failed { background:#e53935; color:white; }
    .status-completed { color:#4caf50; font-weight:bold; }
    .status-failed { color:#e53935; font-weight:bold; }
    .status-pending { color:#ff9800; font-weight:bold; }
  </style>
</head>
<body>
  <h1>🎯 Your Quests for Today (<?php echo $user['goal']; ?> - <?php echo $user['preferences']; ?>)</h1>
  <table>
    <tr>
      <th>ID</th><th>Quest</th><th>Category</th><th>XP</th><th>Plan Category</th><th>Status</th><th>Action</th>
    </tr>
    <?php while ($q = $quests_today->fetch_assoc()) { ?>
      <tr>
        <td><?php echo $q['id']; ?></td>
        <td><?php echo $q['quest_text']; ?></td>
        <td><?php echo $q['category']; ?></td>
        <td><?php echo $q['xp_reward']; ?></td>
        <td><?php echo $q['plan_category']; ?></td>
        <td>
          <?php 
            if ($q['status'] == 'Completed') echo "<span class='status-completed'>✔ Completed</span>";
            elseif ($q['status'] == 'Failed') echo "<span class='status-failed'>✖ Failed</span>";
            else echo "<span class='status-pending'>⏳ Pending</span>";
          ?>
        </td>
        <td>
          <?php if ($q['status'] == 'Pending') { ?>
            <form method="post" action="update_quest.php">
              <input type="hidden" name="quest_id" value="<?php echo $q['id']; ?>">
              <button class="btn complete" name="status" value="Completed">✔ Complete</button>
              <button class="btn failed" name="status" value="Failed">✖ Fail</button>
            </form>
          <?php } else { echo "—"; } ?>
        </td>
      </tr>
    <?php } ?>
  </table>
  <p><a href="dashboard.php" style="color:#00e0ff;">⬅ Back to Dashboard</a></p>
</body>
</html>
