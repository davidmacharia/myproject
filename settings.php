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

// Fetch user information from the database
$query = "SELECT * FROM `Users` WHERE `Email`='$email';";
$result = $connect->query($query);
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['Username'];
    $semail = $user['Email'];
    $contact = $user['Contact'];
    $role = $_SESSION['role'];
}

// Initialize success and error messages
$success_message = "";
$error_message = "";

// Handle form submission to update user settings
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPhone = $_POST['phone'];
    $newBio = $_POST['bio'];
    $newEmail = $_POST['email'];

    // Update user email, phone, and bio in Users table
    $updateQuery = "UPDATE `Users` SET `Contact` = '$newPhone', `Email` = '$newEmail' WHERE `Email` = '$email';";
    $updateBioQuery = "INSERT INTO `UsersProfile` (`Email`, `bio`) 
                       VALUES ('$newEmail', '$newBio') 
                       ON DUPLICATE KEY UPDATE `bio` = '$newBio';";
    
    if ($connect->query($updateQuery) === TRUE && $connect->query($updateBioQuery) === TRUE) {
        $success_message = "Settings updated successfully!";
        // Update the session email after changing the email
        $_SESSION['email'] = $newEmail;
    } else {
        $error_message = "Error updating settings. Please try again.";
    }

    // Handle profile picture upload
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
        $imgFile = $_FILES['profile']['name'];
        $imgTemp = $_FILES['profile']['tmp_name'];
        $imgDir = "Users/";

        // Create directory if it doesn't exist
        if (!file_exists($imgDir)) {
            mkdir($imgDir, 0777, true);
        }

        // Move the uploaded image to the directory
        $imgPath = $imgDir . basename($imgFile);
        if (move_uploaded_file($imgTemp, $imgPath)) {
            // Insert or update the profile image in UsersProfile table
            $updateImage = "INSERT INTO `UsersProfile` (`Email`, `img`) 
                            VALUES ('$newEmail', '$imgPath') 
                            ON DUPLICATE KEY UPDATE `img` = '$imgPath';";
            if ($connect->query($updateImage) === FALSE) {
                $error_message = "Error updating profile picture: " . $connect->error;
            }
        } else {
            $error_message = "Error uploading profile picture.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings - Land Registration System</title>
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

        .settings-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
        }

        .settings-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .settings-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .settings-header img:hover {
            transform: scale(1.05);
        }

        .settings-header h2 {
            font-size: 1.5rem;
            margin-top: 10px;
            color: #333;
        }

        .settings-header p {
            color: #777;
            font-size: 1rem;
        }

        .settings-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .settings-content label {
            font-weight: bold;
            color: #333;
            align-self: center;
        }

        .settings-content input,
        .settings-content textarea,
        .settings-content button {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .settings-content textarea {
            resize: vertical;
            min-height: 120px;
        }

        .settings-content button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .settings-content button:hover {
            background-color: #0056b3;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .actions button {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            border: none;
            transition: background-color 0.3s;
        }

        .actions .delete {
            background-color: #dc3545;
            color: white;
        }

        .actions button:hover {
            background-color: #0056b3;
        }

        .actions .delete:hover {
            background-color: #c82333;
        }

        .upload-btn {
            display: none;
        }

        .error-message,
        .success-message {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
        }

        .success-message {
            color: green;
        }

        @media (max-width: 768px) {
            .settings-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="settings-container">
        <div class="settings-header">
            <img id="profile-img" src="<?php
                $query = "SELECT * FROM `UsersProfile` WHERE `Email`='$email';";
                $result = $connect->query($query);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo $row["img"];
                } else {
                    echo "default.png";
                }
            ?>" alt="Profile Picture" onclick="document.getElementById('pic').click()">
            <input type="file" name="profile" id="pic" class="upload-btn" onchange="previewImage(event)">
            <h2><?php echo $name; ?></h2>
            <p><?php echo $role; ?></p>
        </div>

        <?php if ($error_message) { ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>

        <?php if ($success_message) { ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php } ?>

        <form action="settings.php" method="post" enctype="multipart/form-data">
            <div class="settings-content">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $semail; ?>">

                <label for="phone">Phone Number:</label>
                <input type="tel" name="phone" value="<?php echo $contact; ?>">

                <label for="bio">Bio:</label>
                <textarea name="bio" rows="4"><?php
                    $result = $connect->query("SELECT * FROM `UsersProfile` WHERE `Email`='$email'");
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo htmlspecialchars($row['bio']);
                    }
                ?></textarea>

                <button type="submit">Save Changes</button>
            </div>

            <div class="actions">
                <button type="button" onclick="window.location.href='change-password.php'">Change Password</button>
                <button type="button" class="delete" onclick="window.location.href='delete-account.php'">Delete Account</button>
            </div>
        </form>
    </div>

</body>
</html>
