<?php
session_start();

// ✅ Check if logged in
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
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quest_id = intval($_POST['quest_id']);
    $status   = $_POST['status']; // Completed or Failed

    // ✅ Validate input
    if (!in_array($status, ["Completed", "Failed"])) {
        echo "<script>alert('⚠️ Invalid status.'); window.location.href='quests.php';</script>";
        exit();
    }

    // ✅ Fetch quest
    $sql = "SELECT id, xp_reward, status 
            FROM quests 
            WHERE id = ? AND user_id = ? AND DATE(assigned_at) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quest_id, $user_id);
    $stmt->execute();
    $quest = $stmt->get_result()->fetch_assoc();

    if (!$quest) {
        echo "<script>alert('⚠️ Quest not found.'); window.location.href='quests.php';</script>";
        exit();
    }

    if ($quest['status'] !== "Pending") {
        echo "<script>alert('✅ Quest already {$quest['status']}.'); window.location.href='quests.php';</script>";
        exit();
    }

    // ✅ Update quest status
    $update = $conn->prepare("UPDATE quests SET status=?, completed_at=NOW() WHERE id=?");
    $update->bind_param("si", $status, $quest_id);
    $update->execute();

    // ✅ If completed → add XP and update monthly quests
    if ($status === "Completed") {
        $xp_reward = $quest['xp_reward'];

        $progress = $conn->prepare("UPDATE user_progress 
                                    SET xp = xp + ?, 
                                        level = FLOOR((xp + ?)/1000)+1
                                    WHERE user_id = ?");
        $progress->bind_param("iii", $xp_reward, $xp_reward, $user_id);
        $progress->execute();

        // 🔥 Auto-update monthly quest progress
        include("update_monthly_quest.php");

        echo "<script>alert('🎉 Quest completed! +{$xp_reward} XP. Monthly quest progress updated.'); window.location.href='quests.php';</script>";
    } else {
        echo "<script>alert('❌ Quest marked as Failed.'); window.location.href='quests.php';</script>";
    }
}
$conn->close();
?>
