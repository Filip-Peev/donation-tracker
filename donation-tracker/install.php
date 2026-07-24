<?php
session_start();

function is_installed() {
    $env = __DIR__ . '/.env';
    if (!file_exists($env)) {
        return false;
    }
    $values = [];
    foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $values[trim($k)] = trim(trim($v), "\"' \t\n\r\0\x0B");
    }
    if (empty($values['DB_PASS'])) {
        return false;
    }
    try {
        $pdo = new PDO(
            "mysql:host=" . ($values['DB_HOST'] ?? 'localhost') . ";charset=utf8mb4",
            $values['DB_USER'] ?? 'root',
            $values['DB_PASS'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $dbName = $values['DB_NAME'] ?? 'donation_tracker';
        $tables = $pdo->query("SHOW TABLES FROM $dbName")->fetchAll(PDO::FETCH_COLUMN);
        return in_array('users', $tables);
    } catch (PDOException $e) {
        return false;
    }
}

if (is_installed()) {
    header('Location: /donation-tracker/');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $name = trim($_POST['db_name'] ?? 'donation_tracker');
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = $_POST['db_pass'] ?? '';
    $admin_user = trim($_POST['admin_user'] ?? 'admin');
    $admin_pass = $_POST['admin_pass'] ?? '';
    $admin_name = trim($_POST['admin_name'] ?? 'Administrator');

    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");

        $sql = file_get_contents(__DIR__ . '/donation_tracker.sql');
        $pdo->exec("DROP TABLE IF EXISTS inspections");
        $pdo->exec("DROP TABLE IF EXISTS items");
        $pdo->exec("DROP TABLE IF EXISTS locations");
        $pdo->exec("DROP TABLE IF EXISTS users");

        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt) && stripos($stmt, 'CREATE DATABASE') === false && stripos($stmt, 'USE ') === false && strpos(trim($stmt), '--') !== 0) {
                $pdo->exec($stmt);
            }
        }

        $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, 'admin')
                        ON DUPLICATE KEY UPDATE password_hash = ?, full_name = ?")
             ->execute([$admin_user, $hash, $admin_name, $hash, $admin_name]);

        $env = "DB_HOST=$host\n" .
               "DB_NAME=$name\n" .
               "DB_USER=$user\n" .
               "DB_PASS=$pass\n" .
               "DB_CHARSET=utf8mb4\n" .
               "\n" .
               "APP_NAME=Donation Tracker\n" .
               "APP_URL=http://localhost/donation-tracker\n" .
               "INSPECTION_INTERVAL_MONTHS=3\n";

        file_put_contents(__DIR__ . '/.env', $env);

        $message = "Installation complete! You can now log in with the admin account you created. <a href='login.php'>Go to Login</a>";
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Donation Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width: 600px;">
        <h1 class="mb-4"><i class="bi bi-box-seam"></i> Donation Tracker - Installer</h1>

        <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (empty($message)): ?>
        <form method="POST">
            <h4 class="mb-3">Database Configuration</h4>
            <div class="mb-3">
                <label class="form-label">DB Host</label>
                <input type="text" name="db_host" class="form-control" value="localhost">
            </div>
            <div class="mb-3">
                <label class="form-label">DB Name</label>
                <input type="text" name="db_name" class="form-control" value="donation_tracker">
            </div>
            <div class="mb-3">
                <label class="form-label">DB User</label>
                <input type="text" name="db_user" class="form-control" value="root">
            </div>
            <div class="mb-3">
                <label class="form-label">DB Password</label>
                <input type="password" name="db_pass" class="form-control">
            </div>

            <h4 class="mb-3 mt-4">Admin Account</h4>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="admin_user" class="form-control" value="admin">
            </div>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="admin_name" class="form-control" value="Administrator">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="admin_pass" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">Install</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
