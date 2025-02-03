<?php
session_start();
$database = "landRegistration";
$serverName = "localhost";
$username = "root";
$password = "";
$link = new mysqli($serverName, $username, $password, $database);

if ($link->connect_error) {
    die("Failed to connect: " . $link->connect_error);
}

$mail = $_SESSION['email'];

// Mark as read or delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $notificationId = $_POST['notification_id'];

    if ($action === 'mark_read') {
        $updateQuery = "UPDATE `WelcomeNotification` SET `Reaction` = 'read' WHERE `NotificationId` = ?";
        $stmt = $link->prepare($updateQuery);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $deleteQuery = "DELETE FROM `WelcomeNotification` WHERE `NotificationId` = ?";
        $stmt = $link->prepare($deleteQuery);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch notifications
$sql = "SELECT * FROM `WelcomeNotification` WHERE `Email` = '$mail' ORDER BY `DateRegistered` DESC";
$result = $link->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Add styles as in the original code */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .notification-container {
            width: 80vw;
            max-width: 800px;
            background-color: #ffffff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .header, .footer, .actions {
            text-align: center;
        }
        .actions button {
            padding: 10px 15px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        .actions .mark-read {
            background-color: #28a745;
            color: white;
        }
        .actions .delete {
            background-color: #dc3545;
            color: white;
        }
        .divider {
            width: 100%;
            height: 1px;
            background-color: #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notificationId = $row['NotificationId'];
        $name = $_SESSION['username'];
        $message = $row['Messages'];
        $time = $row['TimeRegistered'];
        $date = $row['DateRegistered'];
        $status = $row['Reaction'];

        echo "
        <div class='notification-container'>
            <div class='header'>
                <img src='nyandarualogo.jpg' alt='County Logo'>
                <h2>County Department Ministry of Lands</h2>
            </div>
            
            <div class='date-time'>
                <p>$date at $time</p>
                <p><strong>Status:</strong> " . ucfirst($status) . "</p>
            </div>
            
            <div class='message'>
                <p><strong>Dear $name,</strong></p>
                <p>$message</p>
            </div>
            
            <div class='actions'>
                <form method='POST' style='display: inline-block;'>
                    <input type='hidden' name='notification_id' value='$notificationId'>
                    <input type='hidden' name='action' value='mark_read'>
                    <button type='submit' class='mark-read'>Mark as Read</button>
                </form>
                <form method='POST' style='display: inline-block;'>
                    <input type='hidden' name='notification_id' value='$notificationId'>
                    <input type='hidden' name='action' value='delete'>
                    <button type='submit' class='delete'>Delete</button>
                </form>
            </div>
            
            <div class='divider'></div>
        </div>
        ";
    }
} else {
    echo "
    <div class='notification-container'>
        <div class='header'>
            
        </div>
        <div class='message'>
            <p>No new notifications at the moment.</p>
        </div>
    </div>
    ";
}
?>
</body>
</html>
