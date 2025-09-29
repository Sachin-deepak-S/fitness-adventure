<?php
// ------------------
// DB connection
// ------------------
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "fitness_db";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

date_default_timezone_set("Asia/Kolkata"); // set your timezone

// ------------------
// 1. Delete yesterday’s quests
// ------------------
$conn->query("DELETE FROM quests WHERE DATE(assigned_at) < CURDATE()");

// ------------------
// 2. Fetch all users
// ------------------
$sql = "SELECT u.id, u.role, u.activity_level 
        FROM users u";
$result = $conn->query($sql);

// ------------------
// Helper: Generate quest pool
// ------------------
function getQuestPool($role, $activity) {
    $multiplier = ($activity == "Beginner") ? 1 : (($activity == "Intermediate") ? 2 : 3);
    $xp = ($activity == "Beginner") ? 50 : (($activity == "Intermediate") ? 100 : 150);
    $quests = [];

    if ($role == "Warrior") {
        $quests = [
            ["Do " . (10*$multiplier) . " push-ups", "Strength", $xp],
            ["Do " . (15*$multiplier) . " squats", "Strength", $xp],
            ["Run " . (1*$multiplier) . " km", "Cardio", $xp],
            ["Hold plank for " . (30*$multiplier) . " sec", "Strength", $xp],
        ];
    } elseif ($role == "Ninja") {
        $quests = [
            ["Do " . (20*$multiplier) . " burpees", "Cardio", $xp],
            ["Sprint for " . (200*$multiplier) . " m", "Agility", $xp],
            ["Do " . (30*$multiplier) . " mountain climbers", "Cardio", $xp],
            ["Jump rope for " . (2*$multiplier) . " min", "Agility", $xp],
        ];
    } elseif ($role == "Monk") {
        $quests = [
            ["Yoga for " . (10*$multiplier) . " min", "Flexibility", $xp],
            ["Stretch hamstrings for " . (5*$multiplier) . " min", "Flexibility", $xp],
            ["Meditate for " . (5*$multiplier) . " min", "Mindfulness", $xp],
            ["Hold warrior pose for " . (60*$multiplier) . " sec", "Flexibility", $xp],
        ];
    }

    shuffle($quests);
    return array_slice($quests, 0, 3); // pick 3 random
}

// ------------------
// 3. Assign new quests
// ------------------
while ($row = $result->fetch_assoc()) {
    $pool = getQuestPool($row['role'], $row['activity_level']);
    foreach ($pool as $q) {
        $stmt = $conn->prepare("INSERT INTO quests (user_id, quest_text, category, xp_reward, assigned_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("issi", $row['id'], $q[0], $q[1], $q[2]);
        $stmt->execute();
    }
}

echo "✅ Daily quests reset successfully at " . date("Y-m-d H:i:s") . "\n";

$conn->close();
?>
