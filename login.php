<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.jpg" alt="Company Logo">
        </div>
        <h2 style=" font-family: 'Times New Roman', Times, serif;">Login</h2>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="employee_id">Employee ID: </label>
                <input type="text" id="employee_id" name="employee_id" required>
                
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                 <input type="password" id="password" name="password" required>
                
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        <div class="bottom-text">
            <p>Don't have an account? <a href="signup.php">Signup here</a></p>
            <p><a href="admin_login.php">Login as Admin</a></p>
        </div>
    </div>
</body>
</html>
