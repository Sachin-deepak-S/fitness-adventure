<?php
session_start();

// if user not logged in -> back to login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$servername = "localhost";
$username   = "root";
$password   = "1234"; // change if needed
$dbname     = "fitness_db";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}

// Handle avatar selection (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avatar_choice'])) {
  $role = $_POST['avatar_choice'];
  $user_id = $_SESSION['user_id'];

  // Map role → avatar file
  $avatarMap = [
    "Warrior" => "warrior.png",
    "Ninja"   => "speed.png",
    "Monk"    => "yoga.png"
  ];
  $avatar = $avatarMap[$role] ?? "default.png";

  $stmt = $conn->prepare("UPDATE users SET role=?, avatar=? WHERE id=?");
  $stmt->bind_param("ssi", $role, $avatar, $user_id);

  if ($stmt->execute()) {
    // redirect to welcome/dashboard
    header("Location: dashboard.php");
    exit();
  } else {
    echo "Error saving role & avatar: " . $stmt->error;
  }
  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitJourney - Pick Your Path</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

    body {
      background: linear-gradient(-45deg, #0f2027, #203a43, #2c5364, #1a1a2e);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
    }

    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .container { text-align: center; width: 95%; max-width: 900px; }

    h1 {
      font-size: 2.4rem;
      color: #00c6fb;
      margin-bottom: 25px;
      text-shadow: 0 0 8px rgba(0,198,251,0.4);
    }

    .cinematic-line { text-shadow: 0 0 6px rgba(255,255,255,0.4); }

    button { box-shadow: 0 3px 10px rgba(0,198,251,0.3); }

    /* Avatar grid */
    .avatar-grid {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 35px;
      margin: 20px 0;
    }

    .avatar-card {
      width: 200px;
      padding: 15px;
      border-radius: 18px;
      background: rgba(255,255,255,0.08);
      cursor: pointer;
      transition: all 0.4s ease;
      border: 3px solid transparent;
      backdrop-filter: blur(6px);
    }

    .avatar-card:hover {
      transform: translateY(-8px) scale(1.05);
    }

    .avatar-card.selected {
      border-color: #00c6fb;
      box-shadow: 0 0 20px rgba(0,198,251,0.7);
      transform: scale(1.08);
    }

    .avatar-card img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
      border: 3px solid #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }

    .avatar-title { font-weight: 600; font-size: 1.2rem; margin-bottom: 5px; }
    .avatar-desc { font-size: 0.9rem; color: #ddd; }

    /* Cinematic */
    #step2 { display: none; }

    .cinematic-line {
      opacity: 0;
      font-size: 1.6rem;
      margin: 18px 0;
      animation: fadeInUp 2s forwards;
      text-shadow: 0 0 15px rgba(255,255,255,0.6);
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    button {
      background: linear-gradient(90deg, #005bea, #00c6fb);
      color: #fff;
      border: none;
      padding: 14px 24px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 30px;
      font-size: 1rem;
      box-shadow: 0 5px 20px rgba(0,198,251,0.5);
    }

    button:hover { transform: scale(1.08); }
  </style>
</head>
<body>
  <form id="avatarForm" method="POST">
    <input type="hidden" name="avatar_choice" id="avatar_choice">
    <div class="container">

      <!-- Step 1: Avatar -->
      <div id="step1">
        <h1>⚔️ Pick Your Avatar</h1>
        <div class="avatar-grid">

          <div class="avatar-card" onclick="selectAvatar(this, 'Warrior')">
            <img src="warrior.png" alt="Warrior Avatar">
            <div class="avatar-title">Warrior</div>
            <div class="avatar-desc">Strength & Power</div>
          </div>

          <div class="avatar-card" onclick="selectAvatar(this, 'Ninja')">
            <img src="speed.png" alt="Ninja Avatar">
            <div class="avatar-title">Ninja</div>
            <div class="avatar-desc">Agility & Speed</div>
          </div>

          <div class="avatar-card" onclick="selectAvatar(this, 'Monk')">
            <img src="yoga.png" alt="Monk Avatar">
            <div class="avatar-title">Monk</div>
            <div class="avatar-desc">Balance & Mindfulness</div>
          </div>

        </div>
      </div>

      <!-- Step 2: Cinematic -->
      <div id="step2">
        <h1>🔥 Your Fitness Journey Begins...</h1>
        <p class="cinematic-line" style="animation-delay: 1s;">💪 Push your limits.</p>
        <p class="cinematic-line" style="animation-delay: 3s;">⚡ Unlock your strength.</p>
        <p class="cinematic-line" style="animation-delay: 5s;">🏆 Become the best version of you.</p>
      </div>

    </div>
  </form>

  <script>
    function selectAvatar(el, role) {
      document.querySelectorAll('.avatar-card').forEach(card => card.classList.remove('selected'));
      el.classList.add('selected');
      document.getElementById('avatar_choice').value = role;

      // after 1.5s -> start cinematic
      setTimeout(() => {
        document.getElementById("step1").style.display = "none";
        document.getElementById("step2").style.display = "block";

        // after cinematic (7s) -> auto submit to save avatar and go to dashboard
        setTimeout(() => {
          document.getElementById('avatarForm').submit();
        }, 7000);
      }, 1500);
    }
  </script>
</body>
</html>
