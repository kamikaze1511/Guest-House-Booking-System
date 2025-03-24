<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.jpg" alt="Company Logo">
        </div>
        <h2 style=" font-family: 'Times New Roman', Times, serif;">Admin Login</h2>
        <form action="admin_verify.php" method="POST">
            <div class="form-group">
                <label for="admin_code">Admin Code:</label>
                <input type="password" id="admin_code" name="admin_code" required>
            </div>
            <div class="form-group">
                <button type="submit">Login as Admin</button>
            </div>
        </form>
        <div class="bottom-text">
            <p><a href="login.php">Back to Employee Login</a></p>
        </div>
    </div>
</body>
</html>
