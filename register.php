<?php 
require_once("receive.php");
// Database configuration
$serverName = "localhost";
$username = "root";
$password = "";

$database = "landRegistration";
// Connect to the database
$link = new mysqli($serverName, $username, $password, $database);
if ($link->connect_error) {
    die("Failed to connect: " . $link->connect_error);
}

$message = ''; // Initialize an empty message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    function validate($data) {
        $data = htmlspecialchars($data);
        $data = stripslashes($data);
        $data = trim($data);
        return $data;
    }

    $role = validate($_POST['account']);
    $username = validate($_POST['username']);
    $email = validate($_POST['email']);
    $contact = validate($_POST['contact']);
    $password = md5(validate($_POST['password'])); // Store hashed password
    $confirm = validate($_POST['confirm']);
    $time = date("H:i:s");
    $date = date("Y-m-d");

    // Check if passwords match
    if ($password !== md5($confirm)) {
        $message = 'Passwords do not match!';
    } else {
        // Insert data into Users table and WelcomeNotification table
        $insertion = "INSERT INTO `Users` (`Account`, `Username`, `Email`, `Contact`, `SecretPin`) 
                      VALUES ('$role', '$username', '$email', '$contact', '$password');";
        $insertion .= " INSERT INTO `Welcomenotification`
         (`Email`, `NotificationType`, `Messages`, `TimeRegistered`, `DateRegistered`, `Reaction`) 
    VALUES ('$email','update', 'Welcome to County Department Ministry of Lands, if you need anything, let us know', '$time', '$date', 'unread');";

        $task = $link->multi_query($insertion);

        if ($task) {
            header("Location: login.html");
        } else {
            $message = 'Account already exists. Try logging in.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Land Registration System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #33ccff, #ff99cc);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        label {
            text-align: left;
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #555;
        }
        select, input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        input[type="password"] {
            letter-spacing: 1px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            font-size: 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
        .links {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }
        .links a {
            color: #007BFF;
            font-size: 0.9rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .links a:hover {
            color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 1rem;
            margin-top: 10px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        function choice() {
            var accountType = document.getElementById("account").value;
            var username = document.getElementById("user").value;
            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;

            if (!username || !email || !password) {
                alert("Please fill in all required fields.");
                return false;
            }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <form action="" method="post" onsubmit="return choice()">
            <h2>Sign Up</h2>

            <!-- Show error message if there is one -->
            <?php if ($message): ?>
                <div class="error-message"><?php echo $message;
                echo "
                 <script>
    function search(){

     setTimeout(()=>{
    location.href='login.html';
    },4000)
}
    search();
    </script>";
                ?></div>
            <?php endif; ?>

            <!-- Account Type -->
            <label for="account">Select Account</label>
            <select id="account" name="account" required>
                <option value="landOwner">Client</option>
                <option value="admin">Admin</option>
                <option value="surveyor">Surveyor</option>
            </select>

            <!-- Username -->
            <input type="text" id="user" name="username" placeholder="Username" required>

            <!-- Email -->
            <input type="email" id="email" name="email" placeholder="Email" required>

            <!-- Contact -->
            <input type="tel" name="contact" placeholder="Contact" required>

            <!-- Password -->
            <input type="password" id="password" name="password" placeholder="Password" minlength="6" maxlength="6" required>

            <!-- Confirm Password -->
            <input type="password" name="confirm" placeholder="Re-type Password" minlength="6" maxlength="6" required>

            <!-- Submit Button -->
            <button type="submit">Submit</button>

            <!-- Links -->
            <div class="links">
                <a href="login.html">Login</a> 
                <a href="forgot-password.html">Forgot Password?</a>
            </div>
        </form>
    </div>
</body>
</html>
