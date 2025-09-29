<?php
session_start();

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "fitness_db";
$port       = 3307;
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname       = trim($_POST['fullname']);
    $email          = trim($_POST['email']);
    $pass           = $_POST['password'];
    $confirm        = $_POST['confirm'];
    $age            = (int)$_POST['age'];
    $gender         = $_POST['gender'];
    $activity_level = $_POST['activity_level'];
    $goal           = $_POST['goal'];
    $preferences    = isset($_POST['preferences']) ? implode(",", $_POST['preferences']) : "";
    $height_cm      = (float)$_POST['height_cm'];
    $weight_kg      = (float)$_POST['weight_kg'];
    $target_goal    = $_POST['target_goal'];

    // ✅ Password match check
    if ($pass !== $confirm) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit();
    }

    // ✅ Email uniqueness check
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists'); window.history.back();</script>";
        exit();
    }
    $check->close();

    // ✅ Hash password
    $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);

    // ✅ Insert into users table (without role)
    $stmt = $conn->prepare("
        INSERT INTO users 
        (fullname, email, password, age, gender, activity_level, goal, preferences, height_cm, weight_kg, target_goal) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssissssdds",
        $fullname,
        $email,
        $hashedPassword,
        $age,
        $gender,
        $activity_level,
        $goal,
        $preferences,
        $height_cm,
        $weight_kg,
        $target_goal
    );

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // ✅ Initialize progress
        $progress = $conn->prepare("
            INSERT INTO user_progress (user_id, xp, level, streak) VALUES (?, 0, 1, 0)
        ");
        $progress->bind_param("i", $user_id);
        $progress->execute();
        $progress->close();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['fullname'] = $fullname;

        header("Location: welcome.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
