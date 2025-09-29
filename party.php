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
$port       = 3307; // ⚠️ Change to 3306 if your MySQL uses default port

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Party System</title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background:#0d1117;
    color:#f0f6fc;
    margin:0;
    padding:0;
}
.container { width:90%; margin:20px auto; }
.section { background:#161b22; padding:20px; border-radius:12px; margin-bottom:20px; box-shadow:0 2px 6px rgba(0,0,0,0.4); }
h2 { color:#00e0ff; margin-bottom:15px; }
h3 { color:#58a6ff; margin-top:15px; }
button {
    padding:6px 14px;
    margin:3px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
    transition:0.2s;
}
button:hover { opacity:0.9; }
.btn-blue { background:#00e0ff; color:#000; }
.btn-green { background:#00ff88; color:#000; }
.btn-red { background:#ff4444; color:#fff; }
.btn-grey { background:#444; color:#fff; }
ul { list-style:none; padding:0; }
li { margin:8px 0; background:#21262d; padding:10px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; }
form { display:inline; margin:0; }
input[type="text"] {
    padding:8px;
    border-radius:6px;
    border:none;
    width:70%;
    margin-bottom:10px;
}
.search-box { display:flex; gap:10px; margin-bottom:15px; }
.search-box input { flex:1; }
</style>
</head>
<body>
<div class="container">

<!-- Back Button -->
<div style="margin-bottom:15px;">
    <a href="dashboard.php"><button class="btn-grey">⬅ Back to Dashboard</button></a>
</div>

<!-- 1️⃣ Section: Party -->
<div class="section">
    <h2>🎉 Party</h2>
    <?php
    // Check if user already in a party
    $stmt = $conn->prepare("SELECT p.id, p.name, pm.role 
                            FROM party_members pm 
                            JOIN parties p ON pm.party_id=p.id 
                            WHERE pm.user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $party = $stmt->get_result()->fetch_assoc();

    if ($party) {
        echo "<p>You are in <b>{$party['name']}</b> as <b>{$party['role']}</b>.</p>";

        // Members
        $members = $conn->prepare("SELECT u.id, u.fullname, pm.role 
                                   FROM party_members pm 
                                   JOIN users u ON pm.user_id=u.id 
                                   WHERE pm.party_id=?");
        $members->bind_param("i",$party['id']);
        $members->execute();
        $result = $members->get_result();

        echo "<div class='member-list'><h3>Members</h3><ul>";
        while ($m = $result->fetch_assoc()) {
            echo "<li><span>{$m['fullname']} ({$m['role']})</span>";

            // Leader options
            if ($party['role'] === "Leader" && $m['id'] != $user_id) {
                echo "
                <form method='POST' action='party_actions.php'>
                    <input type='hidden' name='party_id' value='{$party['id']}'>
                    <input type='hidden' name='member_id' value='{$m['id']}'>
                    <button class='btn-red' type='submit' name='kick_member'>Kick</button>
                </form>
                <form method='POST' action='party_actions.php'>
                    <input type='hidden' name='party_id' value='{$party['id']}'>
                    <input type='hidden' name='member_id' value='{$m['id']}'>
                    <button class='btn-blue' type='submit' name='promote_member'>Promote</button>
                </form>
                <form method='POST' action='party_actions.php'>
                    <input type='hidden' name='party_id' value='{$party['id']}'>
                    <input type='hidden' name='member_id' value='{$m['id']}'>
                    <button class='btn-green' type='submit' name='transfer_leadership'>Make Leader</button>
                </form>
                ";
            }
            echo "</li>";
        }
        echo "</ul></div>";

        // Leave or Disband
        if ($party['role'] === "Leader") {
            echo "<form method='POST' action='party_actions.php'>
                    <input type='hidden' name='party_id' value='{$party['id']}'>
                    <button class='btn-red' type='submit' name='disband_party'>Disband Party</button>
                  </form>";
        } else {
            echo "<form method='POST' action='party_actions.php'>
                    <input type='hidden' name='party_id' value='{$party['id']}'>
                    <button class='btn-grey' type='submit' name='leave_party'>Leave Party</button>
                  </form>";
        }

    } else {
        ?>
        <form method="POST" action="party_actions.php" style="margin-bottom:15px;">
            <input type="text" name="party_name" placeholder="Enter Party Name" required>
            <button class="btn-blue" type="submit" name="create_party">Create Party</button>
        </form>

        <h3>Available Parties</h3>
        <?php
        $res = $conn->query("SELECT p.id, p.name, COUNT(pm.user_id) AS members 
                             FROM parties p 
                             LEFT JOIN party_members pm ON p.id=pm.party_id 
                             GROUP BY p.id");
        if ($res) {
            echo "<ul>";
            while ($row=$res->fetch_assoc()) {
                echo "<li><span>{$row['name']} ({$row['members']} members)</span>
                      <form method='POST' action='party_actions.php'>
                        <input type='hidden' name='party_id' value='{$row['id']}'>
                        <button class='btn-green' type='submit' name='join_party'>Join</button>
                      </form></li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>Error loading parties: ".$conn->error."</p>";
        }
    }
    ?>
</div>

<!-- 2️⃣ Section: Leaderboard -->
<div class="section">
    <h2>🏆 Party Leaderboard</h2>
    <?php
    $sql="SELECT p.name, COUNT(pm.user_id) as members, SUM(up.xp) as total_xp
          FROM parties p
          JOIN party_members pm ON p.id=pm.party_id
          JOIN user_progress up ON pm.user_id=up.user_id
          GROUP BY p.id ORDER BY total_xp DESC LIMIT 10";
    $result=$conn->query($sql);

    if ($result) {
        echo "<ul>";
        $rank = 1;
        while($row=$result->fetch_assoc()){
            $medal = $rank === 1 ? "🥇" : ($rank === 2 ? "🥈" : ($rank === 3 ? "🥉" : "#$rank"));
            echo "<li><span>{$medal} {$row['name']}</span> <span>{$row['members']} members • {$row['total_xp']} XP</span></li>";
            $rank++;
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>Error loading leaderboard: ".$conn->error."</p>";
    }
    ?>
</div>

<!-- 3️⃣ Section: Friends, Search & Invites -->
<div class="section">
    <h2>👥 Friends & Invites</h2>

    <!-- 🔍 Search Users -->
    <div class="search-box">
        <form method="GET" action="party.php" style="display:flex; width:100%;">
            <input type="text" name="search_user" placeholder="🔍 Search users by name..."
                   value="<?php echo isset($_GET['search_user']) ? htmlspecialchars($_GET['search_user']) : ''; ?>">
            <button class="btn-blue" type="submit">Search</button>
        </form>
    </div>

    <?php
    if (isset($_GET['search_user']) && $_GET['search_user'] !== "") {
        $term = "%".$_GET['search_user']."%";
        $sql="SELECT u.id, u.fullname 
              FROM users u 
              WHERE u.fullname LIKE ? AND u.id != ?";
        $stmt=$conn->prepare($sql);
        $stmt->bind_param("si",$term,$user_id);
        $stmt->execute();
        $res=$stmt->get_result();

        echo "<h3>Search Results</h3><ul>";
        if ($res->num_rows > 0) {
            while($u=$res->fetch_assoc()) {
                // Check friendship status
                $check=$conn->prepare("SELECT * FROM friends 
                                        WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?) LIMIT 1");
                $check->bind_param("iiii",$user_id,$u['id'],$u['id'],$user_id);
                $check->execute();
                $relation=$check->get_result()->fetch_assoc();

                echo "<li><span>{$u['fullname']}</span>";

                if (!$relation) {
                    echo "<form method='POST' action='party_actions.php'>
                            <input type='hidden' name='friend_id' value='{$u['id']}'>
                            <button class='btn-blue' type='submit' name='add_friend'>Add Friend</button>
                          </form>";
                } elseif ($relation['status']=="Pending") {
                    echo "<span style='color:#999;'>Pending Request</span>";
                } elseif ($relation['status']=="Accepted") {
                    echo "<form method='POST' action='party_actions.php'>
                            <input type='hidden' name='friend_id' value='{$u['id']}'>
                            <button class='btn-green' type='submit' name='send_invite'>Invite</button>
                          </form>";
                } elseif ($relation['status']=="Declined") {
                    echo "<span style='color:#999;'>Request Declined</span>";
                }

                echo "</li>";
            }
        } else {
            echo "<li>No users found.</li>";
        }
        echo "</ul>";
    }
    ?>

    <!-- Friends, Requests, and Invites remain same as before -->
    <?php
    // (Keep your Friends, Friend Requests, and Party Invites sections unchanged – they are fine)
    ?>
</div>

</div>
</body>
</html>
