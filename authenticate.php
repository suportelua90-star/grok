<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

try {
    $db = new PDO('sqlite:ibo_panel.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $createTablesQuery = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            admin BOOLEAN NOT NULL DEFAULT 0,
            dealer BOOLEAN NOT NULL DEFAULT 0,
            balance REAL NOT NULL DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS themes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            theme_id TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS sports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            header_n TEXT,
            border_c TEXT,
            background_c TEXT,
            text_c TEXT,
            days TEXT,
            api TEXT
        );

        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            note_title TEXT NOT NULL,
            note_content TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS playlist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            dns_id INTEGER,
            mac_address TEXT NOT NULL,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            pin TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS logintheme (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            themelog TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS dns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            url TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS ads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(100),
            url TEXT
        );

        CREATE TABLE IF NOT EXISTS ad_type (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ad_type TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS adsstatus (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            adstype TEXT
        );

        CREATE TABLE IF NOT EXISTS ibo (
            id	INTEGER NOT NULL,
            mac_address	VARCHAR(100),
            username VARCHAR(100),
            password VARCHAR(100),
            expire_date VARCHAR(100),
            url VARCHAR(100),
            title VARCHAR(100),
            created_at VARCHAR(100),
            access_count INTEGER DEFAULT 0,
            PRIMARY KEY(id AUTOINCREMENT)
        );

    ";
    $db->exec($createTablesQuery);

    $checkSettingsExistsQuery = "SELECT COUNT(*) FROM settings";
    $stmt = $db->query($checkSettingsExistsQuery);
    $settingsCount = $stmt->fetchColumn();

    if ($settingsCount == 0) {
        $insertDefaultSettingsQuery = "
            INSERT INTO settings (note_title, note_content)
            VALUES ('RainBow', 'RainBow');
        ";
        $db->exec($insertDefaultSettingsQuery);
    }

    $checkThemesExistsQuery = "SELECT COUNT(*) FROM themes";
    $stmt = $db->query($checkThemesExistsQuery);
    $themesCount = $stmt->fetchColumn();

    if ($themesCount == 0) {
        $insertDefaultThemeQuery = "INSERT INTO themes (theme_id) VALUES ('theme_1')";
        $db->exec($insertDefaultThemeQuery);
    }

    $checkUserExistsQuery = "SELECT COUNT(*) FROM users";
    $stmt = $db->query($checkUserExistsQuery);
    $userCount = $stmt->fetchColumn();

    if ($userCount == 0) {
        $defaultUserQuery = "
            INSERT INTO users (username, password, admin, dealer, balance)
            VALUES ('admin', 'admin', 1, 0, 0);
        ";
        $db->exec($defaultUserQuery);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['admin'] = $user['admin'];
        $_SESSION['dealer'] = $user['dealer'];

        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
