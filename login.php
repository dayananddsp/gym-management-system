<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page"></body>
<div class="login-container">
    <h2> GYM Login</h2>
    <form action="POST">
        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button class="submit-btn" type="submit">Login</button>
    </form>
</div>
</body>
</html>