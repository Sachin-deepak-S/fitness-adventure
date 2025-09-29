<?php
session_start();

// ✅ Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "⚠️ Not logged in";
    exit();
}

$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "fitness_db";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id    = $_SESSION['user_id'];
$monthStart = date("Y-m-01");

// ✅ Fetch monthly quest for this user
$sql = "SELECT * FROM monthly_quests WHERE user_id=? AND assigned_month=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $monthStart);
$stmt->execute();
$monthly = $stmt->get_result()->fetch_assoc();

if (!$monthly) {
    echo "⚠️ No monthly quest found.";
    exit();
}

// ✅ If quest already completed, stop
if ($monthly['status'] === "Completed") {
    echo "✅ Monthly quest already completed!";
    exit();
}

// ✅ Update progress
$newProgress = $monthly['progress_count'] + 1;

if ($newProgress >= $monthly['target_count']) {
    // ✅ Quest completed
    $sql = "UPDATE monthly_quests 
            SET progress_count=?, status='Completed', completed_at=NOW() 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newProgress, $monthly['id']);
    $stmt->execute();

    // ✅ Credit XP reward
    $sql = "UPDATE user_progress 
            SET xp = xp + ?, 
                level = FLOOR((xp + ?)/1000) + 1
            WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $monthly['xp_reward'], $monthly['xp_reward'], $user_id);
    $stmt->execute();

    echo "🎉 Monthly Quest Completed! You earned +{$monthly['xp_reward']} XP.";
} else {
    // ✅ Just update progress
    $sql = "UPDATE monthly_quests SET progress_count=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newProgress, $monthly['id']);
    $stmt->execute();

    echo "📈 Monthly quest progress updated: $newProgress / {$monthly['target_count']}";
}

$conn->close();
?>
