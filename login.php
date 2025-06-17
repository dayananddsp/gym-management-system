<?php
session_start();


if (!isset($_SESSION['client_id']) && isset($_COOKIE['client_id'])) {
    $_SESSION['client_id'] = $_COOKIE['client_id'];
    $_SESSION['role'] = $_COOKIE['role'];
}

// If already logged in, go to dashboard
if (isset($_SESSION['client_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: client.php");
    }
    exit;
}

include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM register WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email); // Only bind email
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows ==1){
        $user = $result->fetch_assoc();
    
        if (password_verify($password, $user['password'])) {

            $_SESSION['client_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            setcookie("client_id", $user['id'], time() + (86400 * 30), "/");
            setcookie("role", $user['role'], time() + (86400 * 30), "/");
            
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                header("Location: admin.php");
                exit();
            }else {
                $_SESSION['client_id'] = $user['id'];
                header("Location: client.php");
                exit();
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Client Not Active";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-container">
    <h2>GYM Login</h2>

    <?php if (!empty($error)) echo "<p class='text-danger' style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button class="submit-btn" type="submit">Login</button>
        <div class="alreadyuser">
            <p>New user <a href="Index.php">Sign in</a></p>
        </div>
    </form>
</div>

</body>
</html>
