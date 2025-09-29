CREATE DATABASE IF NOT EXISTS fitness_db;
USE fitness_db;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Warrior', 'Ninja', 'Monk') NOT NULL DEFAULT 'Monk',
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    activity_level ENUM('Beginner', 'Intermediate', 'Advanced'),
    goal VARCHAR(100),
    preferences TEXT, -- store JSON or comma separated
    height_cm DECIMAL(5,2),
    weight_kg DECIMAL(5,2),
    target_goal VARCHAR(255),
    avatar VARCHAR(255) DEFAULT NULL, -- optional: store path to avatar
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    xp INT DEFAULT 0,
    level INT DEFAULT 1,
    streak INT DEFAULT 0,
    health INT DEFAULT 100,
    stamina INT DEFAULT 100,
    energy INT DEFAULT 100,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
ALTER TABLE user_progress ADD COLUMN last_active DATE DEFAULT NULL;
CREATE TABLE IF NOT EXISTS quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quest_text TEXT NOT NULL,          -- full quest description
    category ENUM('Strength','Cardio','Flexibility','Mindfulness','Mixed') NOT NULL,
    xp_reward INT NOT NULL,
    status ENUM('Pending','Completed','Failed') DEFAULT 'Pending',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
ALTER TABLE quests
ADD COLUMN is_completed TINYINT(1) DEFAULT 0;
ALTER TABLE quests ADD COLUMN plan_category VARCHAR(255) AFTER xp_reward;
CREATE TABLE monthly_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quest_text VARCHAR(255) NOT NULL,
    target_count INT NOT NULL, -- how many times/days it must be done
    progress_count INT DEFAULT 0, -- how much done so far
    xp_reward INT NOT NULL,
    status ENUM('Pending','Completed') DEFAULT 'Pending',
    assigned_month DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
USE fitness_db;

-- Parties
CREATE TABLE parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    leader_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Party Members
CREATE TABLE party_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    party_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('Leader','Co-Leader','Member') DEFAULT 'Member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Party Invites
CREATE TABLE party_invites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    party_id INT NOT NULL,
    invited_user_id INT NOT NULL,
    invited_by INT NOT NULL,
    status ENUM('Pending','Accepted','Declined') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Friends
CREATE TABLE friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('Pending','Accepted','Declined') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE
);
ALTER TABLE party_members
ADD COLUMN xp_at_join INT DEFAULT 0 AFTER role;
ALTER TABLE party_members
ADD UNIQUE KEY unique_party_user (party_id, user_id);
SELECT 
    p.id AS party_id,
    p.name AS party_name,
    SUM(up.xp - pm.xp_at_join) AS total_party_xp,
    COUNT(pm.user_id) AS total_members,
    u_leader.fullname AS leader_name
FROM parties p
JOIN party_members pm ON p.id = pm.party_id
JOIN user_progress up ON pm.user_id = up.user_id
JOIN users u_leader ON p.leader_id = u_leader.id
GROUP BY p.id, p.name, u_leader.fullname
ORDER BY total_party_xp DESC;



ALTER TABLE party_invites
ADD UNIQUE KEY unique_invite (party_id, invited_user_id);

ALTER TABLE user_progress 
ADD COLUMN last_visit DATE DEFAULT CURDATE();
