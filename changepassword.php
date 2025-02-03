<?php
session_start();
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";
$connect = new mysqli($serverName, $username, $password, $db);

if ($connect->error) {
    die("Failed to connect to Database: " . $connect->error);
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the new password and confirm password from form
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate passwords
    if ($newPassword != $confirmPassword) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the password
        $hashedPassword = md5($newPassword);

        // Update password in Users table
        $updatePasswordQuery = "UPDATE `Users` SET `SecretPin` = '$hashedPassword' WHERE `Email` = '$email';";
        
        if ($connect->query($updatePasswordQuery)) {
            $success_message = "Password updated successfully!";
        } else {
            $error_message = "Error updating password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Land Registration System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .change-password-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .change-password-container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #333;
        }

        .change-password-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .change-password-container input {
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .change-password-container button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .change-password-container button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            text-align: center;
            font-size: 1rem;
        }

        .success-message {
            color: green;
            text-align: center;
            font-size: 1rem;
        }

        .password-strength {
            color: #ff0000;
            font-size: 0.9rem;
            text-align: center;
        }

        .password-match {
            color: green;
            font-size: 0.9rem;
            text-align: center;
        }

        .password-mismatch {
            color: red;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="change-password-container">
        <h2>Change Your Password</h2>

        <?php if (isset($error_message)) { ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>

        <?php if (isset($success_message)) { ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php } ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" id="passwordForm">
            <input type="password" name="new_password" placeholder="New Password" minlength="6" id="new_password" maxlength="20" required>
            <div id="password-strength" class="password-strength"></div>

            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" minlength="6" maxlength="20" required>
            <div id="password-status" class="password-match"></div>

            <button type="submit">Change Password</button>
        </form>
    </div>

    <script>
        // Password strength checker
        const newPasswordInput = document.getElementById('new_password');
        const passwordStrengthMessage = document.getElementById('password-strength');

        newPasswordInput.addEventListener('input', function () {
            const password = newPasswordInput.value;
            let strength = 'Weak';

            if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strength = 'Strong';
                passwordStrengthMessage.style.color = 'green';
            } else if (password.length >= 6) {
                strength = 'Medium';
                passwordStrengthMessage.style.color = 'orange';
            } else {
                passwordStrengthMessage.style.color = 'red';
            }

            passwordStrengthMessage.textContent = `Password Strength: ${strength}`;
        });

        // Password match checker
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStatusMessage = document.getElementById('password-status');
        const form = document.getElementById('passwordForm');

        confirmPasswordInput.addEventListener('input', function () {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword === newPassword) {
                passwordStatusMessage.textContent = 'Passwords match';
                passwordStatusMessage.style.color = 'green';
            } else {
                passwordStatusMessage.textContent = 'Passwords do not match';
                passwordStatusMessage.style.color = 'red';
            }
        });
    </script>

</body>
</html>
