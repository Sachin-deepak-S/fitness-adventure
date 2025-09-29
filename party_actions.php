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

/* ---------------- CREATE PARTY ---------------- */
if (isset($_POST['create_party'])) {
    $party_name = trim($_POST['party_name']);

    $stmt = $conn->prepare("INSERT INTO parties (name, leader_id) VALUES (?,?)");
    $stmt->bind_param("si", $party_name, $user_id);
    if ($stmt->execute()) {
        $party_id = $stmt->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO party_members (party_id,user_id,role,xp_at_join) VALUES (?,?, 'Leader', 0)");
        $stmt2->bind_param("ii", $party_id, $user_id);
        $stmt2->execute();
    }
    header("Location: party.php");
    exit();
}

/* ---------------- JOIN PARTY ---------------- */
if (isset($_POST['join_party'])) {
    $party_id = intval($_POST['party_id']);

    $check = $conn->prepare("SELECT party_id FROM party_members WHERE user_id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        $xp = 0;
        $xpQ = $conn->prepare("SELECT xp FROM user_progress WHERE user_id=?");
        $xpQ->bind_param("i", $user_id);
        $xpQ->execute();
        $xpRow = $xpQ->get_result()->fetch_assoc();
        if ($xpRow) $xp = $xpRow['xp'];

        $stmt = $conn->prepare("INSERT INTO party_members (party_id,user_id,role,xp_at_join) VALUES (?,?, 'Member', ?)");
        $stmt->bind_param("iii", $party_id, $user_id, $xp);
        $stmt->execute();
    }
    header("Location: party.php");
    exit();
}

/* ---------------- LEAVE PARTY ---------------- */
if (isset($_POST['leave_party'])) {
    $party_id = intval($_POST['party_id']);
    $stmt = $conn->prepare("DELETE FROM party_members WHERE party_id=? AND user_id=?");
    $stmt->bind_param("ii", $party_id, $user_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

/* ---------------- DISBAND PARTY ---------------- */
if (isset($_POST['disband_party'])) {
    $party_id = intval($_POST['party_id']);
    $stmt = $conn->prepare("DELETE FROM parties WHERE id=? AND leader_id=?");
    $stmt->bind_param("ii", $party_id, $user_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

/* ---------------- KICK MEMBER ---------------- */
if (isset($_POST['kick_member'])) {
    $party_id  = intval($_POST['party_id']);
    $member_id = intval($_POST['member_id']);
    $stmt = $conn->prepare("DELETE FROM party_members WHERE party_id=? AND user_id=?");
    $stmt->bind_param("ii", $party_id, $member_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

/* ---------------- PROMOTE MEMBER ---------------- */
if (isset($_POST['promote_member'])) {
    $party_id  = intval($_POST['party_id']);
    $member_id = intval($_POST['member_id']);
    $stmt = $conn->prepare("UPDATE party_members SET role='Co-Leader' WHERE party_id=? AND user_id=?");
    $stmt->bind_param("ii", $party_id, $member_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

/* ---------------- TRANSFER LEADERSHIP ---------------- */
if (isset($_POST['transfer_leadership'])) {
    $party_id  = intval($_POST['party_id']);
    $member_id = intval($_POST['member_id']);

    $stmt = $conn->prepare("UPDATE parties SET leader_id=? WHERE id=?");
    $stmt->bind_param("ii", $member_id, $party_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE party_members SET role='Member' WHERE party_id=? AND user_id=?");
    $stmt->bind_param("ii", $party_id, $user_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE party_members SET role='Leader' WHERE party_id=? AND user_id=?");
    $stmt->bind_param("ii", $party_id, $member_id);
    $stmt->execute();

    header("Location: party.php");
    exit();
}

/* ---------------- SEND PARTY INVITE ---------------- */
if (isset($_POST['send_invite'])) {
    $friend_id = intval($_POST['friend_id']);

    $stmt = $conn->prepare("SELECT party_id FROM party_members WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $party = $stmt->get_result()->fetch_assoc();

    if ($party) {
        $party_id = $party['party_id'];

        $stmt = $conn->prepare("SELECT party_id FROM party_members WHERE user_id=?");
        $stmt->bind_param("i", $friend_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO party_invites (party_id, invited_user_id, invited_by, status) VALUES (?,?,?, 'Pending')");
            $stmt->bind_param("iii", $party_id, $friend_id, $user_id);
            $stmt->execute();
        }
    }
    header("Location: party.php");
    exit();
}

/* ---------------- ACCEPT PARTY INVITE ---------------- */
if (isset($_POST['accept_invite'])) {
    $invite_id = intval($_POST['invite_id']);

    $stmt = $conn->prepare("SELECT party_id FROM party_invites WHERE id=? AND invited_user_id=? AND status='Pending'");
    $stmt->bind_param("ii", $invite_id, $user_id);
    $stmt->execute();
    $invite = $stmt->get_result()->fetch_assoc();

    if ($invite) {
        $party_id = $invite['party_id'];

        $xp = 0;
        $xpQ = $conn->prepare("SELECT xp FROM user_progress WHERE user_id=?");
        $xpQ->bind_param("i", $user_id);
        $xpQ->execute();
        $xpRow = $xpQ->get_result()->fetch_assoc();
        if ($xpRow) $xp = $xpRow['xp'];

        $stmt = $conn->prepare("INSERT INTO party_members (party_id,user_id,role,xp_at_join) VALUES (?,?, 'Member', ?)");
        $stmt->bind_param("iii", $party_id, $user_id, $xp);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE party_invites SET status='Accepted' WHERE id=?");
        $stmt->bind_param("i", $invite_id);
        $stmt->execute();
    }
    header("Location: party.php");
    exit();
}

/* ---------------- DECLINE PARTY INVITE ---------------- */
if (isset($_POST['decline_invite'])) {
    $invite_id = intval($_POST['invite_id']);
    $stmt = $conn->prepare("UPDATE party_invites SET status='Declined' WHERE id=? AND invited_user_id=?");
    $stmt->bind_param("ii", $invite_id, $user_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

/* ---------------- ADD FRIEND ---------------- */
if (isset($_POST['add_friend'])) {
    $friend_id = intval($_POST['friend_id']);

    // check if already exists
    $stmt = $conn->prepare("SELECT id FROM friends WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?)");
    $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows;

    if ($exists == 0) {
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?,?, 'Pending')");
        $stmt->bind_param("ii", $user_id, $friend_id);
        $stmt->execute();
    }
    header("Location: party.php");
    exit();
}

/* ---------------- ACCEPT FRIEND REQUEST ---------------- */
if (isset($_POST['accept_friend'])) {
    $request_id = intval($_POST['request_id']);
    $stmt = $conn->prepare("UPDATE friends SET status='Accepted' WHERE id=? AND friend_id=?");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

/* ---------------- DECLINE FRIEND REQUEST ---------------- */
if (isset($_POST['decline_friend'])) {
    $request_id = intval($_POST['request_id']);
    $stmt = $conn->prepare("UPDATE friends SET status='Declined' WHERE id=? AND friend_id=?");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    header("Location: party.php");
    exit();
}

?>
