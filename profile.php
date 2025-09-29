<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

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

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT fullname, email, age, gender, goal, preferences, height_cm, weight_kg, target_goal, role, avatar 
        FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Allowed editable fields
$allowed = [
    'fullname'   => 'text',
    'email'      => 'text',
    'age'        => 'number',
    'gender'     => 'text',
    'goal'       => 'text',
    'preferences'=> 'text',
    'height_cm'  => 'number',
    'weight_kg'  => 'number',
    'target_goal'=> 'text'
];

// Handle updates
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['field'])) {
    $field = $_POST['field'];
    $value = trim($_POST['value']);

    if (isset($allowed[$field])) {
        if ($value !== "") {
            if ($allowed[$field] === 'number' && !is_numeric($value)) {
                $msg = "⚠️ $field must be a number.";
            } else {
                $update = $conn->prepare("UPDATE users SET $field=? WHERE id=?");
                $update->bind_param("si", $value, $user_id);
                $update->execute();
                $user[$field] = $value;
                $msg = ucfirst($field) . " updated successfully!";
            }
        } else {
            $msg = "⚠️ Field cannot be empty.";
        }
    }
}

// Handle avatar upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['avatar'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($_FILES["avatar"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ["jpg", "jpeg", "png"];
    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
            $update = $conn->prepare("UPDATE users SET avatar=? WHERE id=?");
            $update->bind_param("si", $targetFile, $user_id);
            $update->execute();
            $user['avatar'] = $targetFile;
            $msg = "✅ Avatar updated successfully!";
        } else {
            $msg = "⚠️ Error uploading file.";
        }
    } else {
        $msg = "⚠️ Only JPG, JPEG, PNG files allowed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Fitness Adventure</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      margin: 0; padding: 0;
      display: flex; justify-content: center; align-items: center;
      min-height: 100vh; color: #fff;
    }
    .profile-card {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      padding: 30px; border-radius: 20px;
      width: 500px; box-shadow: 0 8px 25px rgba(0,0,0,0.5);
      text-align: center;
      animation: fadeIn 0.7s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    h1 { color: #00e0ff; margin-bottom: 25px; font-size: 1.8rem; }
    .avatar-img {
      width: 120px; height: 120px;
      border-radius: 50%;
      border: 4px solid #00e0ff;
      margin-bottom: 15px;
      object-fit: cover;
      transition: transform 0.3s;
    }
    .avatar-img:hover { transform: scale(1.05); }
    .upload-section { margin-bottom: 20px; }
    input[type="file"] { display: none; }
    .upload-btn {
      background: #00e0ff; color: #000;
      padding: 8px 14px; border-radius: 8px;
      cursor: pointer; font-weight: bold;
      display: inline-block;
    }
    .field {
      margin-bottom: 18px;
      padding: 12px;
      background: rgba(0,0,0,0.3);
      border-radius: 10px;
      display: flex; justify-content: space-between; align-items: center;
    }
    .field label { font-weight: bold; color: #ffd200; }
    .field span { flex: 1; margin: 0 10px; text-align: left; }
    .edit-box { display: flex; gap: 8px; align-items: center; }
    input[type="text"], input[type="number"] {
      padding: 6px; border-radius: 6px;
      border: none; width: 120px;
    }
    button {
      padding: 6px 12px;
      background: #00e0ff;
      border: none; border-radius: 6px;
      font-size: 0.9rem; cursor: pointer;
      transition: 0.2s;
    }
    button:hover { background: #00b5cc; }
    .msg { margin-top: 12px; text-align: center; color: #ffd200; }
    .back-link {
      display: block; margin-top: 20px;
      color: #00e0ff; text-decoration: none;
      font-weight: bold; font-size: 1rem;
    }
    .back-link:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="profile-card">
    <h1>👤 Your Profile</h1>

    <!-- Avatar -->
    <?php if (!empty($user['avatar'])): ?>
      <img src="<?php echo htmlspecialchars($user['avatar']); ?>" class="avatar-img" alt="Avatar">
    <?php else: ?>
      <img src="default.png" class="avatar-img" alt="Default Avatar">
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="upload-section">
      <label for="avatar-upload" class="upload-btn">📷 Change Avatar</label>
      <input type="file" id="avatar-upload" name="avatar" onchange="this.form.submit()">
    </form>

    <!-- Profile Fields -->
    <?php if ($user): ?>
      <?php foreach ($user as $field => $value): ?>
        <?php if ($field !== 'avatar'): ?>
          <div class="field">
            <label><?php echo ucfirst($field); ?>:</label>
            <span><?php echo htmlspecialchars($value); ?></span>
            <?php if ($field !== 'role'): ?>
              <form method="POST" class="edit-box">
                <input type="hidden" name="field" value="<?php echo $field; ?>">
                <input type="<?php echo $allowed[$field] === 'number' ? 'number' : 'text'; ?>" 
                       name="value" placeholder="Edit <?php echo $field; ?>">
                <button type="submit">💾</button>
              </form>
            <?php else: ?>
              <span style="color:#ccc; font-style:italic;">(Locked)</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="msg">⚠️ No user data found.</p>
    <?php endif; ?>

    <?php if (isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <a class="back-link" href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
