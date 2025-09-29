<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// DB connection
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "fitness_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get inputs
    $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $fitness_goal = $_POST['fitness_goal'] ?? '';
    $activity_level = $_POST['activity_level'] ?? '';
    $preferences = isset($_POST['preferences']) ? implode(',', $_POST['preferences']) : '';
    $height = (float)($_POST['height'] ?? 0);
    $weight = (float)($_POST['weight'] ?? 0);
    $target_goal = trim($_POST['target_goal'] ?? '');

    // Basic validation
    if ($age < 1 || $age > 120) {
        die("Invalid age.");
    }
    if (!in_array($gender, ['male', 'female', 'other'])) {
        die("Invalid gender.");
    }
    if (!in_array($fitness_goal, ['weight_loss', 'muscle_gain', 'general_health', 'endurance'])) {
        die("Invalid fitness goal.");
    }
    if (!in_array($activity_level, ['beginner', 'intermediate', 'advanced'])) {
        die("Invalid activity level.");
    }
    if ($height < 50 || $height > 250) {
        die("Invalid height.");
    }
    if ($weight < 20 || $weight > 300) {
        die("Invalid weight.");
    }

    // Update user
    $updateSql = "UPDATE users SET age=?, gender=?, fitness_goal=?, activity_level=?, preferences=?, height=?, weight=?, target_goal=? WHERE id=?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("issssddsi", $age, $gender, $fitness_goal, $activity_level, $preferences, $height, $weight, $target_goal, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: dashboard.html"); // Redirect to dashboard
        exit;
    } else {
        die("Error updating profile: " . $stmt->error);
    }
} else {
    // Show form
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fitness Web - Onboarding</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: url("bg.png") no-repeat center center/cover; height: 100vh; display: flex; align-items: center; justify-content: center; }
    .overlay { background: rgba(0,0,0,0.55); width: 100%; height: 100%; position: absolute; top: 0; left: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .logo-box { text-align: center; color: #fff; margin-bottom: 20px; }
    .logo { width: 90px; margin-bottom: 10px; }
    .onboarding-box { background: rgba(255,255,255,0.1); padding: 30px; border-radius: 16px; text-align: center; width: 400px; backdrop-filter: blur(12px); max-height: 80vh; overflow-y: auto; }
    .onboarding-box h2 { color: #fff; margin-bottom: 20px; }
    .onboarding-box input, .onboarding-box select { width: 100%; padding: 10px; margin: 8px 0; border: none; border-radius: 8px; outline: none; }
    .preferences { text-align: left; margin: 10px 0; }
    .preferences label { display: block; color: #fff; margin: 5px 0; }
    .onboarding-box button { width: 100%; padding: 12px; margin-top: 20px; border: none; border-radius: 8px; background: linear-gradient(90deg, #005bea, #00c6fb); color: #fff; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .onboarding-box button:hover { transform: scale(1.05); }
  </style>
</head>
<body>
  <div class="overlay">
    <div class="logo-box">
      <img src="favicon.png" alt="FitJourney Logo" class="logo">
      <h1>FitJourney</h1>
      <p>Let's personalize your experience!</p>
    </div>
    <div class="onboarding-box">
      <h2>Onboarding</h2>
      <form action="onboarding.php" method="post">
        <input type="number" name="age" placeholder="Age" min="1" max="120" required>
        <select name="gender" required>
          <option value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
        <select name="fitness_goal" required>
          <option value="">Select Fitness Goal</option>
          <option value="weight_loss">Weight Loss</option>
          <option value="muscle_gain">Muscle Gain</option>
          <option value="general_health">General Health</option>
          <option value="endurance">Endurance</option>
        </select>
        <select name="activity_level" required>
          <option value="">Select Activity Level</option>
          <option value="beginner">Beginner</option>
          <option value="intermediate">Intermediate</option>
          <option value="advanced">Advanced</option>
        </select>
        <div class="preferences">
          <label>Preferences (select all that apply):</label>
          <label><input type="checkbox" name="preferences[]" value="gym"> Gym Workouts</label>
          <label><input type="checkbox" name="preferences[]" value="home"> Home Workouts</label>
          <label><input type="checkbox" name="preferences[]" value="yoga"> Yoga</label>
          <label><input type="checkbox" name="preferences[]" value="running"> Running</label>
          <label><input type="checkbox" name="preferences[]" value="cycling"> Cycling</label>
        </div>
        <input type="number" name="height" placeholder="Height (cm)" min="50" max="250" required>
        <input type="number" name="weight" placeholder="Weight (kg)" min="20" max="300" step="0.1" required>
        <input type="text" name="target_goal" placeholder="Target Goal (e.g., Lose 5kg in 3 months)" required>
        <button type="submit">Complete Onboarding</button>
      </form>
    </div>
  </div>
</body>
</html>
<?php
}
$conn->close();
?>
