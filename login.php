<?php
// ----------------------
// DB connection settings
// ----------------------
$servername = "localhost";
$username   = "root";
$password   = "1234";   // your MySQL root password
$dbname     = "fitness_db";
$port       = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start(); // Start session

// ----------------------
// Login request handling
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Validate
    if (empty($email) || empty($pass)) {
        showNotification("⚠️ Please fill all required fields.", "login.html", "#ff9800");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        showNotification("⚠️ Invalid email address.", "login.html", "#ff9800");
    }

    // Fetch user
    $checkSql = "SELECT id, fullname, password FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            // ✅ Login success
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['fullname']  = $user['fullname'];

            // 🔥 Streak logic
            $today     = date("Y-m-d");
            $yesterday = date("Y-m-d", strtotime("-1 day"));

            $progressSql = "SELECT streak, last_active FROM user_progress WHERE user_id=?";
            $progressStmt = $conn->prepare($progressSql);
            $progressStmt->bind_param("i", $user['id']);
            $progressStmt->execute();
            $progressResult = $progressStmt->get_result();

            if ($progressResult->num_rows === 1) {
                $progressData = $progressResult->fetch_assoc();
                $currentStreak = $progressData['streak'] ?? 0;
                $lastActive    = $progressData['last_active'] ?? null;

                if ($lastActive === $yesterday) {
                    $currentStreak++;
                } elseif ($lastActive !== $today) {
                    $currentStreak = 1;
                }

                $update = $conn->prepare("UPDATE user_progress SET streak=?, last_active=? WHERE user_id=?");
                $update->bind_param("isi", $currentStreak, $today, $user['id']);
                $update->execute();
                $update->close();
            } else {
                // If no row exists, create one
                $insert = $conn->prepare("INSERT INTO user_progress (user_id, streak, last_active) VALUES (?, 1, ?)");
                $insert->bind_param("is", $user['id'], $today);
                $insert->execute();
                $insert->close();
            }

            $checkStmt->close();
            $conn->close();

            showNotification("✅ Login successful! Redirecting to dashboard...", "dashboard.php", "#4CAF50");
        } else {
            // ❌ Wrong password
            $checkStmt->close();
            $conn->close();
            showNotification("❌ Invalid password. Please try again.", "login.html", "#ff4d4d");
        }
    } else {
        $checkStmt->close();
        $conn->close();
        showNotification("❌ No account found. Please sign up!", "signup.html", "#ff9800");
    }
} else {
    header("Location: login.html");
    exit;
}

// ----------------------
// Helper: Notification
// ----------------------
function showNotification($msg, $redirect, $bgColor) {
    echo "
    <html>
    <head>
        <style>
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: {$bgColor};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                font-family: Arial, sans-serif;
                font-size: 16px;
                box-shadow: 0px 4px 8px rgba(0,0,0,0.2);
                animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
            }
            @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
            @keyframes fadeOut { from {opacity: 1;} to {opacity: 0;} }
        </style>
    </head>
    <body>
        <div class='notification'>{$msg}</div>
        <script>
            setTimeout(function(){
                window.location.href = '{$redirect}';
            }, 3000);
        </script>
    </body>
    </html>";
    exit;
}
?>
