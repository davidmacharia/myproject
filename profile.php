<?php
session_start();

$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

$connect = new mysqli($serverName, $username, $password, $db);

if ($connect->connect_error) {
    die("Failed to connect to Database: " . $connect->connect_error);
}

if (!isset($_SESSION['email'])) {
    die("Unauthorized access.");
}

$email = $_SESSION['email'];
$name = $semail = $contact = "";

// Fetch user information from the database
$sale = "SELECT * FROM `Users` WHERE `Email` = ?";
$stmt = $connect->prepare($sale);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['Username'];
    $semail = $row['Email'];
    $contact = $row['Contact'];
}
$stmt->close();

// Handle form submission to update user info
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPhone = $_POST['phone'];
    $newBio = $_POST['bio'];
    $newEmail = $_POST['email'];

    // Update or insert bio in UsersProfile table
    $updateBio = "INSERT INTO `UsersProfile` (`Email`, `bio`) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE `bio` = ?";
    $stmt = $connect->prepare($updateBio);
    $stmt->bind_param("sss", $email, $newBio, $newBio);
    if (!$stmt->execute()) {
        echo "Error updating bio: " . $stmt->error;
    }
    $stmt->close();

    // Update phone and email in Users table
    $updatePhone = "UPDATE `Users` SET `Contact` = ?, `Email` = ? WHERE `Email` = ?";
    $stmt = $connect->prepare($updatePhone);
    $stmt->bind_param("sss", $newPhone, $newEmail, $email);
    if (!$stmt->execute()) {
        echo "Error updating phone number or email: " . $stmt->error;
    }
    $stmt->close();

    // Handle profile image upload
    if (isset($_FILES['profile']['tmp_name']) && !empty($_FILES['profile']['tmp_name'])) {
        $uploadsDir = "Users/";
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        $imgTemp = $_FILES['profile']['tmp_name'];
        $imgName = uniqid() . "_" . basename($_FILES['profile']['name']); // Generate a unique file name
        $imgPath = $uploadsDir . $imgName;

        if (move_uploaded_file($imgTemp, $imgPath)) {
            $stmt = $connect->prepare("INSERT INTO `UsersProfile` (`Email`, `img`) VALUES (?, ?) 
                                       ON DUPLICATE KEY UPDATE `img` = ?");
            $stmt->bind_param("sss", $email, $imgPath, $imgPath);
            if (!$stmt->execute()) {
                echo "Error updating profile picture: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error uploading profile picture.";
        }
    }

    echo "<script>alert('Profile updated successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Land Registration System</title>
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
            height:auto;
            color: #333;
        }

        .profile-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            height: auto;
            overflow: auto;
            max-width:100%;
        }

        .profile-header {
            text-align: center;
            padding-top: 2px;
            background:#fcda;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .profile-header img:hover {
            transform: scale(1.05);
        }

        .profile-header h2 {
            font-size: 1.5rem;
            margin-top: 10px;
            color: #333;
        }

        .profile-header p {
            color: #777;
            font-size: 1rem;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 10px;
        }

        .profile-content label {
            font-weight: bold;
            color: #333;
            align-self: center;
        }

        .profile-content input,
        .profile-content textarea,
        .profile-content button {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .profile-content textarea {
            resize: vertical;
            min-height:60px;
        }

        .profile-content button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .profile-content button:hover {
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

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <div class="profile-header">
            <img id="profile-img" src="
            <?php
                $query = "SELECT `img` FROM `UsersProfile` WHERE `Email` = ?";
                $stmt = $connect->prepare($query);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo $row["img"];
                } else {
                    echo "default.png";
                }
                $stmt->close();
            ?>" alt="Profile Picture" onclick="document.getElementById('pic').click()">
            <input type="file" name="profile" id="pic" class="upload-btn" onchange="previewImage(event)">
            <h2><?php echo htmlspecialchars($name); ?></h2>
            <p><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'User'; ?></p>
        </div>

       
            <div class="profile-content">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($semail); ?>">

                <label for="phone">Phone Number:</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($contact); ?>">

                <label for="bio">Bio:</label>
                <textarea name="bio" rows="4"><?php
                    $bioQuery = "SELECT `bio` FROM `UsersProfile` WHERE `Email` = ?";
                    $stmt = $connect->prepare($bioQuery);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo htmlspecialchars($row['bio']);
                    }
                    $stmt->close();
                ?></textarea>

                <button type="submit">Save Changes</button>
            </div>

            <div class="actions">
                <button type="button" onclick="window.location.href='changepassword.php'">Change Password</button>
                <button type="button" class="delete" onclick="window.location.href='delete-account.php'">Delete Account</button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('profile-img');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
