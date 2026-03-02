<?php

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/db.php';

$connection = getDbConnection();

$result = $connection->query('SELECT COUNT(*) AS cnt FROM users');
$row = $result ? $result->fetch_assoc() : null;

if ($row && (int)$row['cnt'] === 0) {
    $defaultUsername = 'admin';
    $defaultPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);

    $statement = $connection->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    if ($statement) {
        $statement->bind_param('ss', $defaultUsername, $defaultPasswordHash);
        $statement->execute();
        $statement->close();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $statement = $connection->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');

        if ($statement) {
            $statement->bind_param('s', $username);
            $statement->execute();
            $result = $statement->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $statement->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Unable to process login right now.';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - Student Record</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-circle">SR</div>
                <div>
                    <div class="auth-title">Student Record Portal</div>
                    <div class="auth-subtitle">Sign in to manage student records</div>
                </div>
            </div>

            <?php if ($error !== ''): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="auth-body">
                <form method="post" action="login.php" autocomplete="off">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" autocomplete="username" autofocus>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" autocomplete="current-password">
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn-primary">Sign in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
