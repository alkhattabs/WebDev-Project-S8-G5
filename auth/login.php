<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Bandar Hadhramaut POS</title>
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="login-container">
        <h1>Log In to Bandar Hadhramaut POS</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter Your Username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter Your Password" required>
            </div>

            <button type="submit" class="login-btn">Log In</button>
        </form>
    </div>
</body>
</html> 